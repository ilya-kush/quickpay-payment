<?php
/**
 *  PaymentId
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    26.10.2021
 * Time:    20:09
 */
namespace HW\QuickPay\Gateway\Request;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
/**
 *
 */
class PaymentId extends AbstractRequest {

	/**
	 * @inheritDoc
	 */
	public function build(array $buildSubject) {

        /** It is used in command fetch_transaction_info */
        if(isset($buildSubject['transactionId']) && $buildSubject['transactionId']){
            return [
                'id' => $this->_parseLastTransactionId($buildSubject['transactionId'])
            ];
        }

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];

        /** second part supports payments created by old version module */
        $gatewayPaymentId = $this->_getGatewayPaymentId($paymentDO->getPayment());

        return [
            'id' => $gatewayPaymentId
        ];
	}
}
