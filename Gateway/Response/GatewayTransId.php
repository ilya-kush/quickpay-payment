<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

class GatewayTransId extends AbstractHandler
{
    protected function processResponsePayment(ResponseObject $responsePayment, array $handlingSubject): void
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        /** @var $payment OrderPayment */
        $payment = $paymentDO->getPayment();

        //$payment->setLastTransId($responsePayment->getId());
        $payment->setAdditionalInformation('Gateway trans id', $responsePayment->getId());
        $this->addPaymentAdditionalData(
            $payment,
            ConfigProvider::PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE,
            $responsePayment->getId()
        );
    }
}
