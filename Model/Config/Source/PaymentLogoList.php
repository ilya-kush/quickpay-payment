<?php
/**
 *  PaymentLogoList
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    10.10.2021
 * Time:    21:25
 */
namespace HW\QuickPay\Model\Config\Source;
/**
 *
 */
class PaymentLogoList implements \Magento\Framework\Data\OptionSourceInterface {

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>' => '<label>'), ...)
     */
    public function toOptionArray(){
        $result = [];
        foreach ($this->getValuesArray() as $value => $label ){
            $result[$value] = ['label' => $label, 'value' => $value];
        }
        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function getValuesArray() {
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
