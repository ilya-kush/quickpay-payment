<?php
/**
 *  Synchronized
 *
 * @copyright Copyright © 2022 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    06.01.2022
 * Time:    11:27
 */
namespace HW\QuickPay\Model\Payment\Method\Specification;
/**
 *
 */
class Synchronized extends Group {

    const SYNCHRONIZED_METHOD_FLAG_CODE = 'synchronized_method';

	/**
	 * @inheritDoc
	 */
	public function isSatisfiedBy($paymentMethod) {
		if(parent::isSatisfiedBy($paymentMethod)){
            foreach ($this->_scopeConfig->getValue('payment') as $code => $data) {
                if($paymentMethod == $code){
                    if (isset($data[self::SYNCHRONIZED_METHOD_FLAG_CODE]) && $data[self::SYNCHRONIZED_METHOD_FLAG_CODE]) {
                        return true;
                    }
                }
            }
        }
        return false;
	}
}
