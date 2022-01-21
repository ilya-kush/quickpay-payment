<?php
/**
 * StateResolver
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir ilya.kush@gmail.com
 * Date:    17.12.2021
 * Time:    11:42
 */
namespace HW\QuickPay\Plugin\Sales\Model\Order;
use HW\QuickPay\Model\Payment\Method\Specification\Group;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

/**
 *
 */
class StateResolver {
    /**
     * @var Group
     */
    protected $_groupSpecification;

    /**
     * @param Group $groupSpecification
     */
    public function __construct(Group $groupSpecification) {
        $this->_groupSpecification = $groupSpecification;
    }

    /**
     * @param \Magento\Sales\Model\Order\StateResolver $subject
     * @param string                                   $result
     * @param OrderInterface                           $order
     * @param array                                    $arguments
     *
     * @return string
     */
	public function afterGetStateForOrder(\Magento\Sales\Model\Order\StateResolver $subject, $result,OrderInterface $order, array $arguments = []) {
        if($this->_groupSpecification->isSatisfiedBy($order->getPayment()->getMethod())){
            if($order->getPayment()->getIsTransactionPending() || $order->isPaymentReview()){
                return Order::STATE_PAYMENT_REVIEW;
            }
        }
        return $result;
    }
}
