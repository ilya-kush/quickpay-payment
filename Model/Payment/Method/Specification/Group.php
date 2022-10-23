<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Model\Payment\Method\Specification;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\SpecificationInterface;

class Group implements SpecificationInterface
{
    public const QUICKPAY_GROUP_CODE = 'quickpay_group';

    protected ScopeConfigInterface $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isSatisfiedBy($paymentMethod): bool
    {
        foreach ($this->scopeConfig->getValue('payment') as $code => $data) {
            if ($paymentMethod == $code) {
                if (isset($data['group']) && $data['group'] == self::QUICKPAY_GROUP_CODE) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getGroupMethods(): array
    {
        $methods = [];
        foreach ($this->scopeConfig->getValue('payment') as $code => $data) {
            if (isset($data['group']) && $data['group'] == self::QUICKPAY_GROUP_CODE) {
                $methods[] = $code;
            }
        }
        return $methods;
    }
}
