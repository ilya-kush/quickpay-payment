<?php
/**
 *  AbstractSpecification
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    08.11.2021
 * Time:    12:33
 */
namespace HW\QuickPay\Model\Payment\Method\Specification;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\SpecificationInterface;
use Magento\Store\Model\ScopeInterface;

/**
 *
 */
class Group implements SpecificationInterface{

    CONST QUICKPAY_GROUP_CODE = 'quickpay_group';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
	 * @inheritDoc
	 */
	public function isSatisfiedBy($paymentMethod) {
        foreach ($this->_scopeConfig->getValue('payment') as $code => $data) {
            if($paymentMethod == $code){
                if (isset($data['group']) && $data['group'] == self::QUICKPAY_GROUP_CODE) {
                    return true;
                }
            }
        }
        return false;
	}

    /**
     * @return array
     */
    public function getGroupMethods(){
        $methods = [];
        foreach ($this->_scopeConfig->getValue('payment') as $code => $data) {
            if (isset($data['group']) && $data['group'] == self::QUICKPAY_GROUP_CODE) {
                $methods[] = $code;
            }
        }

        return $methods;
    }
}
