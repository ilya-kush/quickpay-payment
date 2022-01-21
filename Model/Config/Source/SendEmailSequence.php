<?php
/**
 *  SendEmailSequence
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    09.11.2021
 * Time:    20:57
 */
namespace HW\QuickPay\Model\Config\Source;
use Magento\Framework\Data\OptionSourceInterface;
/**
 *
 */
class SendEmailSequence implements OptionSourceInterface {

	/**
	 * @inheritDoc
	 */
	public function toOptionArray() {
        $options = [
            0 => __('After payment is authorized'),
            1 => __('After order is created (default Magento logic)')
        ];
        $result = [];
        foreach ($options as $value => $label ){
            $result[$value] = ['label' => $label, 'value' => $value];
        }
        return $result;
	}
}
