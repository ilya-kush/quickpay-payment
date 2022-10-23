<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use HW\QuickPay\Model\Payment\Method\Specification\Group as GroupSpecification;
use Magento\Payment\Helper\Data;

class MethodsList implements OptionSourceInterface
{
    protected Data $paymentHelper;
    protected GroupSpecification $gatewaySpecification;

    public function __construct(
        Data $paymentHelper,
        GroupSpecification $gatewaySpecification
    ) {
        $this->paymentHelper          = $paymentHelper;
        $this->gatewaySpecification = $gatewaySpecification;
    }

    public function toOptionArray(): array
    {
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
