<?php
/**
 * @author  Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Observer\Sales\Order\Payment;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Payment\Method\Specification\Group;

class PlaceStart implements ObserverInterface
{
    protected Group $_specificationGroup;
    protected Data $_helper;

    public function __construct(
        Data  $helper,
        Group $specificationGroup
    ) {
        $this->_specificationGroup = $specificationGroup;
        $this->_helper             = $helper;
    }

    public function execute(Observer $observer): void
    {
        /** @var Payment $payment */
        $payment = $observer->getPayment();

        if ($this->_specificationGroup->isSatisfiedBy($payment->getMethod())) {
            $order = $payment->getOrder();
            $emailSend = $this->_helper->ifSendOrderConformationEmailByDefaultMagentoLogic($order->getStoreId());
            /** @var Order $order */
            $order->setCanSendNewEmailFlag($emailSend)
                ->setIsCustomerNotified($emailSend);
        }
    }
}
