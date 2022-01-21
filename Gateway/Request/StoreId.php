<?php
/**
 *  StoreId
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    11.11.2021
 * Time:    16:23
 */
namespace HW\QuickPay\Gateway\Request;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
/**
 *
 */
class StoreId extends AbstractRequest {
	/**
	 * @inheritDoc
	 */
	public function build(array $buildSubject) {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        return [
            'store_id' => $payment->getOrder()->getStoreId()
        ];
	}
}
