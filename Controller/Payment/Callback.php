<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Controller\Payment;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Invoice\NotifierInterface as InvoiceNotifierInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Operations\AuthorizeOperation;
use Magento\Sales\Model\Order\Payment\Operations\SaleOperation;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use HW\QuickPay\Gateway\Helper\Operation;
use HW\QuickPay\Gateway\Helper\ResponseConverter;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Helper\Data;

class Callback implements HttpPostActionInterface, CsrfAwareActionInterface
{
    protected RequestInterface $_request;
    protected ResponseInterface $_response;
    protected Data $_helper;
    protected Logger $_logger;
    /**
     * @var Order|null
     */
    protected $_order = null;
    protected OrderFactory $_orderFactory;
    protected ResultFactory $_resultFactory;
    protected OrderRepository $_orderRepository;
    protected OrderSender $_orderSender;
    protected SaleOperation $_saleOperation;
    protected InvoiceNotifierInterface $_notifierInvoice;
    protected ResponseConverter $_responseConverter;
    protected Operation $_paymentOperationHelper;
    protected AuthorizeOperation $_authorizeOperation;

    public function __construct(
        ResultFactory                     $resultFactory,
        OrderRepository                   $orderRepository,
        OrderFactory                      $orderFactory,
        OrderSender                       $orderSender,
        InvoiceNotifierInterface          $notifierInvoice,
        RequestInterface                  $request,
        ResponseInterface                 $response,
        ResponseConverter                 $responseConverter,
        Operation                         $paymentOperationHelper,
        Data                              $helper,
        Logger                            $logger,
        AuthorizeOperation                $authorizeOperation,
        SaleOperation                     $saleOperation
    ) {
        $this->_logger          = $logger;
        $this->_helper          = $helper;
        $this->_response        = $response;
        $this->_request         = $request;
        $this->_orderFactory    = $orderFactory;
        $this->_resultFactory   = $resultFactory;
        $this->_orderRepository = $orderRepository;
        $this->_orderSender     = $orderSender;
        $this->_saleOperation   = $saleOperation;
        $this->_notifierInvoice = $notifierInvoice;
        $this->_responseConverter = $responseConverter;
        $this->_paymentOperationHelper = $paymentOperationHelper;
        $this->_authorizeOperation = $authorizeOperation;
    }

    /**
	 * @return ResponseInterface|ResultInterface|void
	 */
	public function execute() {

        $content = $this->_request->getContent();
        /** here we use php function json_decode!!! */
        $responsePaymentArray  = json_decode($content,true);
        $this->_logger->debug(['callback' => 'Run callback controller']);
        $this->_logger->debug($responsePaymentArray);

        if (!($this->_order && $this->_order->getId())) {
            return $this->_resultFactory->create(ResultFactory::TYPE_RAW);
        }

        $responsePayment = $this->_responseConverter->convertArrayToObject($responsePaymentArray);

        if ($this->_isAutoCaptureResponse($responsePayment)
            && !$this->_existCaptureOperation($responsePayment) ) {
            $this->_logger->debug([$responsePayment->getId() => 'autocapture callback without capture operation']);
            return $this->_resultFactory->create(ResultFactory::TYPE_RAW);
        }

        if ($this->_order->getId() ) {
            /** @var Payment $payment */
            $payment = $this->_order->getPayment();
            $storeId = $this->_order->getStoreId();

            if ($this->_order->isCanceled()) {
                $payment->addTransactionCommentsToOrder(
                    $responsePayment->getId(),
                    __('Cannot process payment. Order is canceled.')
                );
                try {
                    $payment->void(new DataObject());
                } catch (\Exception $exception ) {
                    $this->_logger->debug(['Callback Exception' => $exception->getMessage()]);
                }
            }
            if (in_array($this->_order->getState(),
                [
                    Data::INITIALIZED_PAYMENT_ORDER_STATE_VALUE,
                    Order::STATE_PAYMENT_REVIEW,
                    Order::STATE_NEW //<- to support payment created by old module
                ])) {
                try {
                    $totalDue = $this->_order->getTotalDue();
                    $baseTotalDue = $this->_order->getBaseTotalDue();

                    if ($this->_existCaptureOperation($responsePayment,true)) {
                        if ($this->_order->canInvoice()) {
                            $payment->setAmountAuthorized($totalDue);
                            $payment->setBaseAmountAuthorized($baseTotalDue);
                            $this->_saleOperation->execute($payment);
                            if ($this->_helper->ifSendInvoiceEmail($storeId)) {
                                /** @var Order\Invoice $invoice */
                                $invoice = $payment->getCreatedInvoice();
                                $this->_notifierInvoice->notify($this->_order, $invoice);
                            }
                        } else {
                            $payment->addTransactionCommentsToOrder(
                                $responsePayment->getId(),
                                __('Order has been already invoiced.')
                            );
                        }
                    } else {
                        $this->_authorizeOperation->authorize($payment,true,$baseTotalDue);
                        // base amount will be set inside
                        $payment->setAmountAuthorized($totalDue);

                        if ($this->_existCaptureOperation($responsePayment)) {
                            $payment->addTransactionCommentsToOrder(
                                $responsePayment->getId(),
                                __('There was an unsuccess autocapture operation.')
                            );
                        }
                    }

                    if (!$this->_order->getEmailSent()) {
                        $this->_orderSender->send($this->_order);
                        $payment->addTransactionCommentsToOrder(
                            $responsePayment->getId(),
                            __('The order confirmation email was sent')
                        );
                    }
                } catch (\Exception $exception ) {
                    $this->_logger->debug(['Callback Exception' => $exception->getMessage()]);
                }
            }
            else {
                $payment->addTransactionCommentsToOrder(
                    $responsePayment->getId(),
                    __('Cannot process payment. Order has been already processed.')
                );
            }
            $this->_orderRepository->save($this->_order);
        }
        return $this->_resultFactory->create(ResultFactory::TYPE_RAW);
	}

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $this->_logger->debug([sprintf("Rejected request %s :", $request->getActionName()) => $request->getParams()]);
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        $content = $this->_request->getContent();
        $this->_response->setBody('OK');
        /** here we use php function json_decode!!! */
        $responsePaymentArray  = json_decode($content,true);

        if (!isset($responsePaymentArray['order_id']) || !isset($responsePaymentArray['id'])) {
            $this->_logger->debug(['Exception' => 'no id or order id']);
            return false;
        }

        $orderIncrementId = $responsePaymentArray['order_id'];
        /** @var Order $order */
        $this->_order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);

        if ($this->_order && $this->_order->getId()) {
            $submittedChecksum  = $request->getServer('HTTP_QUICKPAY_CHECKSUM_SHA256');
            //Fetch private key from config and validate checksum
            $key = $this->_helper->getPrivateKey($this->_order->getStoreId());
            $checksum = hash_hmac('sha256', $content, $key);
            if ($checksum == $submittedChecksum) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param ResponseObject $responsePayment
     */
    protected function _isAutoCaptureResponse($responsePayment): bool
    {
        return $responsePayment->getLink() && $responsePayment->getLink()->getAutoCapture();
    }

    /**
     * @param ResponseObject $responsePayment
     */
    protected function _existCaptureOperation($responsePayment, bool $onlyApploved = false): bool
    {
        if (is_array($responsePayment->getOperations())) {
            foreach ($responsePayment->getOperations() as $operation) {
                if ($this->_paymentOperationHelper->isOperationCapture($operation)) {
                    if ($onlyApploved) {
                        if ($this->_paymentOperationHelper->isStatusCodeApproved($operation)) {
                            return true;
                        };
                    } else {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @deprecated since 3.1.0
     */
    protected function _prepareComment(string $paymentId, string $message): string
    {
        return sprintf(__("%s Transaction ID: \"%s\""),$message, $paymentId);
    }
}
