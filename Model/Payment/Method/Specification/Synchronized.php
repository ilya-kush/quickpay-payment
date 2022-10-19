<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Model\Payment\Method\Specification;

class Synchronized extends Group
{
    public const SYNCHRONIZED_METHOD_FLAG_CODE = 'synchronized_method';

	public function isSatisfiedBy($paymentMethod): bool
    {
		if (parent::isSatisfiedBy($paymentMethod)) {
            foreach ($this->_scopeConfig->getValue('payment') as $code => $data) {
                if ($paymentMethod == $code) {
                    if (isset($data[self::SYNCHRONIZED_METHOD_FLAG_CODE])
                        && $data[self::SYNCHRONIZED_METHOD_FLAG_CODE]) {
                        return true;
                    }
                }
            }
        }
        return false;
	}
}
