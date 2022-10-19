<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

class PaymentLinkHandler extends AbstractHandler
{
	protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject): void
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        /** @var $payment OrderPayment */
        $payment = $paymentDO->getPayment();

        $this->_addPaymentAdditionalData(
            $payment,
            ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE,
            $responsePayment->getLink()->getUrl()
        );
	}
}
