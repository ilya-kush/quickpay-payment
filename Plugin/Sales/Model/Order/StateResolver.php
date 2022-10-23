<?php
/**
 * @author  Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Plugin\Sales\Model\Order;

use HW\QuickPay\Model\Payment\Method\Specification\Group;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

class StateResolver
{
    protected Group $groupSpecification;

    public function __construct(Group $groupSpecification)
    {
        $this->groupSpecification = $groupSpecification;
    }

    public function afterGetStateForOrder(
        \Magento\Sales\Model\Order\StateResolver $subject,
        string $result,
        OrderInterface $order,
        array $arguments = []
    ): string {
        if ($this->groupSpecification->isSatisfiedBy($order->getPayment()->getMethod())) {
            if ($order->getPayment()->getIsTransactionPending() || $order->isPaymentReview()) {
                return Order::STATE_PAYMENT_REVIEW;
            }
        }
        return $result;
    }
}
