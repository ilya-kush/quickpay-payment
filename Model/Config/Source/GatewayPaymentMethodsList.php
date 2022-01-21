<?php
/**
 *  GatewayPaymentMethodsList
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    10.10.2021
 * Time:    21:30
 */
namespace HW\QuickPay\Model\Config\Source;
/**
 *
 */
class GatewayPaymentMethodsList implements \Magento\Framework\Data\OptionSourceInterface {
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
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
            ''           => __('All Payment Methods'),
            'creditcard' => __('All Creditcards'),
            'specified'  => __('As Specified')
        ];
    }
}
