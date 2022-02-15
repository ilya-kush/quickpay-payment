<?php
/**
 * Callback
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    20.10.2021
 * Time:    13:27
 */
namespace HW\QuickPay\Controller\Payment;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
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

/**
 * Class Callback
 *
 * @package
 */
class Callback implements HttpPostActionInterface, CsrfAwareActionInterface {
//class Callback implements ActionInterface {
    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
     * @var ResponseInterface
     */
    protected $_response;
    /**
     * @var Data
     */
    protected $_helper;
    /**
     * @var Logger
     */
    protected Logger $_logger;
    /**
     * @var Order|null
     */
    protected $_order = null;
    /**
     * @var OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var ResultFactory
     */
    protected $_resultFactory;
    /**
     * @var OrderRepository
     */
    protected $_orderRepository;
    /**
     * @var OrderSender
     */
    protected $_orderSender;
    /**
     * @var SaleOperation
     */
    protected $_saleOperation;
    /**
     * @var InvoiceNotifierInterface
     */
    protected $_notifierInvoice;
    /**
     * @var ResponseConverter
     */
    protected $_responseConverter;
    /**
     * @var Operation
     */
    protected $_paymentOperationHelper;
    /**
     * @var AuthorizeOperation
     */
    protected $_authorizeOperation;

    /**
     * @param ResultFactory            $resultFactory
     * @param OrderRepository          $orderRepository
     * @param OrderFactory             $orderFactory
     * @param OrderSender              $orderSender
     * @param InvoiceNotifierInterface $notifierInvoice
     * @param RequestInterface         $request
     * @param ResponseInterface        $response
     * @param ResponseConverter        $responseConverter
     * @param Operation                $paymentOperationHelper
     * @param Data                     $helper
     * @param Logger                   $logger
     * @param AuthorizeOperation       $authorizeOperation
     * @param SaleOperation            $saleOperation
     */
    public function __construct(
        ResultFactory                     $resultFactory,
        OrderRepository                   $orderRepository,
        OrderFactory $orderFactory,
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
	 * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
	 */
	public function execute() {

        $content = $this->_request->getContent();
        /** here we use php function json_decode!!! */
        $responsePaymentArray  = json_decode($content,true);
        $this->_logger->debug(['callback' => 'Run callback controller']);
        $this->_logger->debug($responsePaymentArray);

        if(!($this->_order && $this->_order->getId())) {
            return $this->_resultFactory->create(ResultFactory::TYPE_RAW);
        }


        //$responsePaymentArray = ['id' =>'277199783', 'order_id'=> 'DEVPA000000125', 'test_mode' => true];
//        $responsePaymentArray = [
//            'id' =>'277607048',
//            'order_id'=> 'DEVPA000000146',
//            'test_mode' => true,
//            'link' => ['auto_capture'=> true],
//            'operations' => [
//                0 => ['id'=> 1, 'type'=>'authorize'],
//                1 => ['id'=> 2, 'type'=>'capture'],
//            ],
//        ]; echo '<code><pre>';

        $responsePayment = $this->_responseConverter->convertArrayToObject($responsePaymentArray);

        if($this->_isAutoCaptureResponse($responsePayment) && !$this->_existCaptureOperation($responsePayment) ){
            $this->_logger->debug([$responsePayment->getId() => 'autocapture callback without capture operation']);
            return $this->_resultFactory->create(ResultFactory::TYPE_RAW);
        }

        if($this->_order->getId() ){
            /** @var Payment $payment */
            $payment = $this->_order->getPayment();
            $storeId = $this->_order->getStoreId();

            if($this->_order->isCanceled()) {
                $payment->addTransactionCommentsToOrder($responsePayment->getId(),__('Cannot process payment. Order is canceled.'));
                try {
                    $payment->void(new DataObject());
                } catch (\Exception $exception ){
                    $this->_logger->debug(['Callback Exception' => $exception->getMessage()]);
                }
            }
            if(in_array($this->_order->getState(),
                [
                    Data::INITIALIZED_PAYMENT_ORDER_STATE_VALUE,
                    Order::STATE_PAYMENT_REVIEW,
                    Order::STATE_NEW //<- to support payment created by old module
                ])) {
                try {
                    $totalDue = $this->_order->getTotalDue();
                    $baseTotalDue = $this->_order->getBaseTotalDue();

                    if($this->_existCaptureOperation($responsePayment,true)){
                        if ($this->_order->canInvoice()) {
                            $payment->setAmountAuthorized($totalDue);
                            $payment->setBaseAmountAuthorized($baseTotalDue);
                            $this->_saleOperation->execute($payment);
                            if($this->_helper->ifSendInvoiceEmail($storeId)){
                                /** @var Order\Invoice $invoice */
                                $invoice = $payment->getCreatedInvoice();
                                $this->_notifierInvoice->notify($this->_order, $invoice);
                            }
                        } else {
                            $payment->addTransactionCommentsToOrder($responsePayment->getId(),__('Order has been already invoiced.'));
                        }
                    } else {
                        $this->_authorizeOperation->authorize($payment,true,$baseTotalDue);
                        // base amount will be set inside
                        $payment->setAmountAuthorized($totalDue);

                        if($this->_existCaptureOperation($responsePayment)){
                            $payment->addTransactionCommentsToOrder($responsePayment->getId(),__('There was an unsuccess autocapture operation.'));
                        }
                    }

                    if (!$this->_order->getEmailSent()) {
                        $this->_orderSender->send($this->_order);
                        $payment->addTransactionCommentsToOrder($responsePayment->getId(),__('The order confirmation email was sent'));
                    }
                } catch (\Exception $exception ){
                    $this->_logger->debug(['Callback Exception' => $exception->getMessage()]);
                }
            }
            else {
                $payment->addTransactionCommentsToOrder($responsePayment->getId(),__('Cannot process payment. Order has been already processed.'));
            }
            $this->_orderRepository->save($this->_order);
        }
        return $this->_resultFactory->create(ResultFactory::TYPE_RAW);
	}

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException {
        $this->_logger->debug([sprintf("Rejected request %s :", $request->getActionName()) => $request->getParams()]);
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool {

        $content = $this->_request->getContent();
        $this->_response->setBody('OK');
        /** here we use php function json_decode!!! */
        $responsePaymentArray  = json_decode($content,true);

        if(!isset($responsePaymentArray['order_id']) || !isset($responsePaymentArray['id'])){
            $this->_logger->debug(['Exception' => 'no id or order id']);
            return false;
        }

        $orderIncrementId = $responsePaymentArray['order_id'];
        /** @var Order $order */
        $this->_order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);

        if($this->_order && $this->_order->getId()){
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
     *
     * @return bool
     */
    protected function _isAutoCaptureResponse($responsePayment):bool {
        return $responsePayment->getLink() && $responsePayment->getLink()->getAutoCapture();
    }

    /**
     * @param ResponseObject $responsePayment
     * @param bool           $onlyApploved
     *
     * @return bool
     */
    protected function _existCaptureOperation($responsePayment,$onlyApploved = false):bool {
        if(is_array($responsePayment->getOperations())){
            foreach ($responsePayment->getOperations() as $operation){
                if($this->_paymentOperationHelper->isOperationCapture($operation)){
                    if($onlyApploved){
                        if($this->_paymentOperationHelper->isStatusCodeApproved($operation)){
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
     * @param string $paymentId
     * @param string $message
     *
     * @return string
     */
    protected function _prepareComment($paymentId, string $message){
        return sprintf(__("%s Transaction ID: \"%s\""),$message, $paymentId);
    }
}
