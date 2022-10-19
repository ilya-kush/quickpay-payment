<?php
/**
 * @author  Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Observer\Sales;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use HW\QuickPay\Model\Payment\Method\Specification\Group;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

class OrderCancelAfter implements ObserverInterface
{
    protected SerializerInterface $_serializer;
    protected Group $_specification;

    public function __construct(
        Group $specification,
        SerializerInterface $serializer
    ) {
        $this->_serializer    = $serializer;
        $this->_specification = $specification;
    }

    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        $payment = $order->getPayment();

        if ($this->_specification->isSatisfiedBy($payment->getMethod())) {
            $additional = $payment->getAdditionalData();
            if ($additional) {
                $additional = $this->_serializer->unserialize($additional);
                unset($additional[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE]);
            }
            $payment->setAdditionalData($this->_serializer->serialize($additional));
        }
    }
}
