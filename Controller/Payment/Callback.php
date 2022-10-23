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
    protected RequestInterface $request;
    protected ResponseInterface $response;
    protected Data $helper;
    protected Logger $logger;
    /**
     * @var Order|null
     */
    protected $order = null;
    protected OrderFactory $orderFactory;
    protected ResultFactory $resultFactory;
    protected OrderRepository $orderRepository;
    protected OrderSender $orderSender;
    protected SaleOperation $saleOperation;
    protected InvoiceNotifierInterface $notifierInvoice;
    protected ResponseConverter $responseConverter;
    protected Operation $paymentOperationHelper;
    protected AuthorizeOperation $authorizeOperation;

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
        $this->logger = $logger;
        $this->helper = $helper;
        $this->response = $response;
        $this->request         = $request;
        $this->orderFactory    = $orderFactory;
        $this->resultFactory   = $resultFactory;
        $this->orderRepository = $orderRepository;
        $this->orderSender     = $orderSender;
        $this->saleOperation   = $saleOperation;
        $this->notifierInvoice = $notifierInvoice;
        $this->responseConverter = $responseConverter;
        $this->paymentOperationHelper = $paymentOperationHelper;
        $this->authorizeOperation = $authorizeOperation;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {

        $content = $this->request->getContent();
        /** here we use php function json_decode!!! */
        $responsePaymentArray  = json_decode($content, true);
        $this->logger->debug(['callback' => 'Run callback controller']);
        $this->logger->debug($responsePaymentArray);

        if (!($this->order && $this->order->getId())) {
            return $this->resultFactory->create(ResultFactory::TYPE_RAW);
        }

        $responsePayment = $this->responseConverter->convertArrayToObject($responsePaymentArray);

        if ($this->isAutoCaptureResponse($responsePayment)
            && !$this->existCaptureOperation($responsePayment)) {
            $this->logger->debug([$responsePayment->getId() => 'autocapture callback without capture operation']);
            return $this->resultFactory->create(ResultFactory::TYPE_RAW);
        }

        if ($this->order->getId()) {
            /** @var Payment $payment */
            $payment = $this->order->getPayment();
            $storeId = $this->order->getStoreId();

            if ($this->order->isCanceled()) {
                $payment->addTransactionCommentsToOrder(
                    $responsePayment->getId(),
                    __('Cannot process payment. Order is canceled.')
                );
                try {
                    $payment->void(new DataObject());
                } catch (\Exception $exception) {
                    $this->logger->debug(['Callback Exception' => $exception->getMessage()]);
                }
            }
            if (in_array(
                $this->order->getState(),
                [
                    Data::INITIALIZED_PAYMENT_ORDER_STATE_VALUE,
                    Order::STATE_PAYMENT_REVIEW,
                    Order::STATE_NEW //<- to support payment created by old module
                ]
            )) {
                try {
                    $totalDue = $this->order->getTotalDue();
                    $baseTotalDue = $this->order->getBaseTotalDue();

                    if ($this->existCaptureOperation($responsePayment, true)) {
                        if ($this->order->canInvoice()) {
                            $payment->setAmountAuthorized($totalDue);
                            $payment->setBaseAmountAuthorized($baseTotalDue);
                            $this->saleOperation->execute($payment);
                            if ($this->helper->ifSendInvoiceEmail($storeId)) {
                                /** @var Order\Invoice $invoice */
                                $invoice = $payment->getCreatedInvoice();
                                $this->notifierInvoice->notify($this->order, $invoice);
                            }
                        } else {
                            $payment->addTransactionCommentsToOrder(
                                $responsePayment->getId(),
                                __('Order has been already invoiced.')
                            );
                        }
                    } else {
                        $this->authorizeOperation->authorize($payment, true, $baseTotalDue);
                        // base amount will be set inside
                        $payment->setAmountAuthorized($totalDue);

                        if ($this->existCaptureOperation($responsePayment)) {
                            $payment->addTransactionCommentsToOrder(
                                $responsePayment->getId(),
                                __('There was an unsuccess autocapture operation.')
                            );
                        }
                    }

                    if (!$this->order->getEmailSent()) {
                        $this->orderSender->send($this->order);
                        $payment->addTransactionCommentsToOrder(
                            $responsePayment->getId(),
                            __('The order confirmation email was sent')
                        );
                    }
                } catch (\Exception $exception) {
                    $this->logger->debug(['Callback Exception' => $exception->getMessage()]);
                }
            } else {
                $payment->addTransactionCommentsToOrder(
                    $responsePayment->getId(),
                    __('Cannot process payment. Order has been already processed.')
                );
            }
            $this->orderRepository->save($this->order);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_RAW);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $this->logger->debug([sprintf("Rejected request %s :", $request->getActionName()) => $request->getParams()]);
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        $content = $this->request->getContent();
        $this->response->setBody('OK');
        /** here we use php function json_decode!!! */
        $responsePaymentArray  = json_decode($content, true);

        if (!isset($responsePaymentArray['order_id']) || !isset($responsePaymentArray['id'])) {
            $this->logger->debug(['Exception' => 'no id or order id']);
            return false;
        }

        $orderIncrementId = $responsePaymentArray['order_id'];
        /** @var Order $order */
        $this->order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);

        if ($this->order && $this->order->getId()) {
            $submittedChecksum  = $request->getServer('HTTP_QUICKPAY_CHECKSUM_SHA256');
            //Fetch private key from config and validate checksum
            $key = $this->helper->getPrivateKey($this->order->getStoreId());
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
    protected function isAutoCaptureResponse($responsePayment): bool
    {
        return $responsePayment->getLink() && $responsePayment->getLink()->getAutoCapture();
    }

    /**
     * @param ResponseObject $responsePayment
     */
    protected function existCaptureOperation($responsePayment, bool $onlyApploved = false): bool
    {
        if (is_array($responsePayment->getOperations())) {
            foreach ($responsePayment->getOperations() as $operation) {
                if ($this->paymentOperationHelper->isOperationCapture($operation)) {
                    if ($onlyApploved) {
                        if ($this->paymentOperationHelper->isStatusCodeApproved($operation)) {
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
    protected function prepareComment(string $paymentId, string $message): string
    {
        return sprintf(__("%s Transaction ID: \"%s\""), $message, $paymentId);
    }
}
