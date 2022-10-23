<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PaymentLogoList implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->getValuesArray() as $value => $label) {
            $result[$value] = ['label' => $label, 'value' => $value];
        }
        return $result;
    }

    public function getValuesArray(): array
    {
        return [
            'dankort'            => __('Dankort'),
            'forbrugsforeningen' => __('Forbrugsforeningen'),
            'visa'               => __('VISA'),
            'visaelectron'       => __('VISA Electron'),
            'mastercard'         => __('MasterCard'),
            'maestro'            => __('Maestro'),
            'jcb'                => __('JCB'),
            'diners'             => __('Diners Club'),
            'amex'               => __('AMEX'),
            'sofort'             => __('Sofort'),
            'viabill'            => __('ViaBill'),
            'mobilepay'          => __('MobilePay'),
            'paypal'             => __('Paypal'),
            'applepay'           => __('Apple Pay')
        ];
    }
}
