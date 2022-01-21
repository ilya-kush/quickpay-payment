<?php
/**
 *  MethodsList
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    08.11.2021
 * Time:    12:45
 */
namespace HW\QuickPay\Model\Config\Source;
use Magento\Framework\Data\OptionSourceInterface;
use HW\QuickPay\Model\Payment\Method\Specification\Group as GroupSpecification;
/**
 *
 */
class MethodsList implements OptionSourceInterface {
    /** @var  \Magento\Payment\Helper\Data */
    protected $paymentHelper;

    /**
     * @var GroupSpecification
     */
    protected $gatewaySpecification;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param GroupSpecification           $gatewaySpecification
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        GroupSpecification $gatewaySpecification
    ) {
        $this->paymentHelper          = $paymentHelper;
        $this->gatewaySpecification = $gatewaySpecification;
    }
	/**
	 * @inheritDoc
	 */
	public function toOptionArray() {
        $result = [];
        foreach ($this->paymentHelper->getPaymentMethods() as $code => $data) {
            if ($this->gatewaySpecification->isSatisfiedBy($code)) {
                $result[] = [
                    'label' => $data['title'] ?? $this->paymentHelper->getMethodInstance($code)->getTitle(),
                    'value' => $code
                ];
            }
        }
        return $result;
	}
}
