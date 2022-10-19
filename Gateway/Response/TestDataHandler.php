<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Api\Data\Gateway\Response\PaymentModelInterface;
use HW\QuickPay\Gateway\Helper\ResponseConverter;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Helper\Data;

class TestDataHandler extends AbstractHandler
{
    protected Data $_helper;

    public function __construct(
        SerializerInterface $serializer,
        ResponseConverter $responseConverter,
        Data $helper
    ) {
        parent::__construct($serializer,$responseConverter);
        $this->_helper = $helper;
    }


	protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject): void
    {
        if ($responsePayment->getType() != PaymentModelInterface::MODEL_TYPE_PAYMENT) {
            return;
        }

        if ($responsePayment->getTestMode()) {
            /** @var PaymentDataObjectInterface $paymentDO */
            $paymentDO = $handlingSubject['payment'];
            /** @var $payment OrderPayment */
            $payment = $paymentDO->getPayment();

            $order = $payment->getOrder();
            $storeId = $order->getStoreId();
            if (!$this->_helper->isTestMode($storeId)) {
                $payment->setIsTransactionPending(true);
                $payment->setIsTransactionApproved(false);
                $payment->setIsFraudDetected(true);
                $payment->addTransactionCommentsToOrder(
                    $handlingSubject['transactionId'] ?? $payment->getTransactionId(),
                    __('Order attempted paid with test card!!!!')
                );
            } else {
                if ($order->isPaymentReview()) {
                    /** here we make sure other handlers not sent pending  */
                    $this->_checkAndSetIsTransactionPendingFalse($payment);
                    /** here we make sure other handlers not rejected approving  */
                    $this->_checkAndSetIsTransactionApprovedTrue($payment);
                    if ($order->isFraudDetected()) {
                        /** here we make sure other handlers not detected fraud  */
                        $this->_checkAndSetIsFraudDetectedFalse($payment);
                    }
                    $payment->addTransactionCommentsToOrder(
                        $handlingSubject['transactionId'] ?? $payment->getTransactionId(),
                        __('Now is test mode. Test card was accepted.')
                    );
                }
            }
        }
	}
}
