<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class SendEmailSequence implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [
            0 => __('After payment is authorized'),
            1 => __('After order is created (default Magento logic)')
        ];
        $result = [];
        foreach ($options as $value => $label) {
            $result[$value] = ['label' => $label, 'value' => $value];
        }
        return $result;
    }
}
