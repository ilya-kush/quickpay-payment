<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class GatewayPaymentMethodsList implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->getValuesArray() as $value => $label ) {
            $result[$value] = ['label' => $label, 'value' => $value];
        }
        return $result;
    }

    public function getValuesArray(): array
    {
        return [
            ''           => __('All Payment Methods'),
            'creditcard' => __('All Creditcards'),
            'specified'  => __('As Specified')
        ];
    }
}
