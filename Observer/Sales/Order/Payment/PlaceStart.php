<?php
/**
 * PlaceStart
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author  Ilya Kushnir ilya.kush@gmail.com
 * Date:    10.11.2021
 * Time:    10:57
 */
namespace HW\QuickPay\Observer\Sales\Order\Payment;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Payment\Method\Specification\Group;
/**
 *
 */
class PlaceStart implements ObserverInterface {
    /**
     * @var Group
     */
    protected $_specificationGroup;
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @param Data  $helper
     * @param Group $specificationGroup
     */
    public function __construct(
        Data  $helper,
        Group $specificationGroup
    ) {
        $this->_specificationGroup = $specificationGroup;
        $this->_helper             = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var Payment $payment */
        $payment = $observer->getPayment();

        if($this->_specificationGroup->isSatisfiedBy($payment->getMethod())){
            $order = $payment->getOrder();
            $emailSend = $this->_helper->ifSendOrderConformationEmailByDefaultMagentoLogic($order->getStoreId());
            /** @var Order $order */
            $order->setCanSendNewEmailFlag($emailSend)
                ->setIsCustomerNotified($emailSend);
        }
    }
}
