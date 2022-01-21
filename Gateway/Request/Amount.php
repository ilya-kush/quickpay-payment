<?php
/**
 *  Amount
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    26.10.2021
 * Time:    20:10
 */
namespace HW\QuickPay\Gateway\Request;
/**
 *
 */
class Amount extends AbstractRequest {
	/**
	 * @inheritDoc
	 */
	public function build(array $buildSubject) {
        if (!isset($buildSubject['amount'])
            || ($buildSubject['amount'] <= 0)
        ) {
            throw new \InvalidArgumentException('Wrong amount');
        }

        $amount  = $buildSubject['amount'];
        return [
            'amount' => $this->_amountConverter->convert($amount)
        ];
	}
}
