<?php
/**
 *  GatewayTransId
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    12.10.2021
 * Time:    13:59
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;
/**
 *
 */
class GatewayTransId extends AbstractHandler{
	/**
	 * @inheritDoc
	 */
	protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject) {

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        /** @var $payment OrderPayment */
        $payment = $paymentDO->getPayment();

        //$payment->setLastTransId($responsePayment->getId());
        $payment->setAdditionalInformation('Gateway trans id', $responsePayment->getId());
        $this->_addPaymentAdditionalData($payment,ConfigProvider::PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE,$responsePayment->getId());
	}
}
