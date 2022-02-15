<?php
/**
 *  AbstractHandler
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    12.10.2021
 * Time:    13:47
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseConverter;
use HW\QuickPay\Gateway\Helper\ResponseObject;
/**
 *
 */
abstract class AbstractHandler implements HandlerInterface {

    /**
     * @var SerializerInterface
     */
    protected $_serializer;
    /**
     * @var ResponseConverter
     */
    protected $_responseConverter;

    /**
     * @param SerializerInterface $serializer
     * @param ResponseConverter   $responseConverter
     */
    public function __construct(
        SerializerInterface $serializer,
        ResponseConverter $responseConverter
    ){
        $this->_serializer        = $serializer;
        $this->_responseConverter = $responseConverter;
    }

    /**
	 * @inheritDoc
	 */
	public function handle(array $handlingSubject, array $response) {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $responsePayment = $this->_responseConverter->convertArrayToObject($response);
        $this->_processResponsePayment($responsePayment, $handlingSubject);
	}

    /**
     * @param OrderPayment $payment
     * @param string       $key
     * @param              $value
     */
    protected function _addPaymentAdditionalData(OrderPayment $payment, string $key, $value): void {
        $additional = $payment->getAdditionalData();
        if (!$additional) {
            $additional = [];
        } else {
            $additional = $this->_serializer->unserialize($additional);
        }
        if($value){
            $additional[$key] = $value;
        } else {
            unset($additional[$key]);
        }
        $payment->setAdditionalData($this->_serializer->serialize($additional));
    }

    /**
     * We make sure other handlers not sent pending
     *
     * @param OrderPayment $payment
     *
     * @return OrderPayment
     */
    protected function _checkAndSetIsTransactionPendingFalse($payment){
        if($payment->getIsTransactionPending() !== true){
            $payment->setIsTransactionPending(false);
        }
        return $payment;
    }

    /**
     * We make sure other handlers not rejected approving
     * @param OrderPayment $payment
     *
     * @return OrderPayment
     */
    protected function _checkAndSetIsTransactionApprovedTrue($payment){
        if($payment->getIsTransactionApproved() !== false){
            $payment->setIsTransactionApproved(true);
        }
        return $payment;
    }

    /**
     * We make sure other handlers not detected fraud
     *
     * @param OrderPayment $payment
     *
     * @return OrderPayment
     */
    protected function _checkAndSetIsFraudDetectedFalse($payment){
        if ($payment->getIsFraudDetected() !== true) {
            $payment->setIsFraudDetected(false);
        }
        return $payment;
    }


    /**
     * @param ResponseObject $responsePayment
     * @param array          $handlingSubject
     *
     * @return void
     */
    abstract protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject);
}
