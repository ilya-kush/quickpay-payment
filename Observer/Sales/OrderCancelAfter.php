<?php
/**
 * OrderCancelAfter
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir ilya.kush@gmail.com
 * Date:    09.11.2021
 * Time:    13:36
 */
namespace HW\QuickPay\Observer\Sales;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use HW\QuickPay\Model\Payment\Method\Specification\Group;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

/**
 *
 */
class OrderCancelAfter implements ObserverInterface {
    /**
     * @var SerializerInterface
     */
    protected $_serializer;
    /**
     * @var Group
     */
    protected $_specification;

    /**
     * @param Group               $specification
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Group $specification,
        SerializerInterface $serializer
    ) {
        $this->_serializer    = $serializer;
        $this->_specification = $specification;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var Order $order */
        $order = $observer->getOrder();
        $payment = $order->getPayment();

        if($this->_specification->isSatisfiedBy($payment->getMethod())){
            $additional = $payment->getAdditionalData();
            if ($additional) {
                $additional = $this->_serializer->unserialize($additional);
                unset($additional[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE]);
            }
            $payment->setAdditionalData($this->_serializer->serialize($additional));
        }
    }
}
