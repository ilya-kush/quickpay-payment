<?php
/**
 *  PaymentLinkHandler
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    15.10.2021
 * Time:    16:36
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;
/**
 *
 */
class PaymentLinkHandler extends AbstractHandler {
	/**
	 * @inheritDoc
	 */
	protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject) {

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        /** @var $payment OrderPayment */
        $payment = $paymentDO->getPayment();

        $this->_addPaymentAdditionalData($payment,ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE,$responsePayment->getLink()->getUrl());
	}
}
