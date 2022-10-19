<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseConverter;
use HW\QuickPay\Gateway\Helper\ResponseObject;

abstract class AbstractHandler implements HandlerInterface
{
    protected SerializerInterface $_serializer;
    protected ResponseConverter $_responseConverter;

    public function __construct(
        SerializerInterface $serializer,
        ResponseConverter $responseConverter
    ) {
        $this->_serializer        = $serializer;
        $this->_responseConverter = $responseConverter;
    }

	public function handle(array $handlingSubject, array $response): void
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $responsePayment = $this->_responseConverter->convertArrayToObject($response);
        $this->_processResponsePayment($responsePayment, $handlingSubject);
	}

    protected function _addPaymentAdditionalData(OrderPayment $payment, string $key, $value): void
    {
        $additional = $payment->getAdditionalData();
        if (!$additional) {
            $additional = [];
        } else {
            $additional = $this->_serializer->unserialize($additional);
        }
        if ($value) {
            $additional[$key] = $value;
        } else {
            unset($additional[$key]);
        }
        $payment->setAdditionalData($this->_serializer->serialize($additional));
    }

    /**
     * We make sure other handlers not sent pending
     */
    protected function _checkAndSetIsTransactionPendingFalse(OrderPayment $payment): OrderPayment
    {
        if ($payment->getIsTransactionPending() !== true) {
            $payment->setIsTransactionPending(false);
        }
        return $payment;
    }

    /**
     * We make sure other handlers not rejected approving
     */
    protected function _checkAndSetIsTransactionApprovedTrue(OrderPayment $payment): OrderPayment
    {
        if ($payment->getIsTransactionApproved() !== false) {
            $payment->setIsTransactionApproved(true);
        }
        return $payment;
    }

    /**
     * We make sure other handlers not detected fraud
     */
    protected function _checkAndSetIsFraudDetectedFalse(OrderPayment $payment): OrderPayment
    {
        if ($payment->getIsFraudDetected() !== true) {
            $payment->setIsFraudDetected(false);
        }
        return $payment;
    }

    /**
     * @param ResponseObject $responsePayment
     */
    abstract protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject);
}
