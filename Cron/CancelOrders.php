<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Cron;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Payment\Method\Specification\Group;
use Magento\Framework\Stdlib\DateTime as FrameworkDateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class CancelOrders
{
    private CONST MIN_TIMEOUT_VALUE = 5;

    protected Data $_helper;
    protected TimezoneInterface $_localeDate;
    protected OrderRepository $_orderRepository;
    protected CollectionFactory $_orderCollectionFactory;
    protected Group $_groupSpecification;

    public function __construct(
        OrderRepository $orderRepository,
        Group $groupSpecification,
        CollectionFactory $orderCollectionFactory,
        TimezoneInterface $localeDate,
        Data $helper
    ) {
        $this->_helper = $helper;
        $this->_localeDate = $localeDate;
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_groupSpecification = $groupSpecification;
    }

    public function execute(): bool
    {
        $timeOutValue = $this->_helper->getCancelTimeout();
        if (!$timeOutValue) { return true; }
        $timeOutValue = ($timeOutValue < self::MIN_TIMEOUT_VALUE)?self::MIN_TIMEOUT_VALUE:$timeOutValue;

        /** third param is important. We need GMT date.  */
        $currentDate = $this->_localeDate->date(null,null,false);
        $timeOutDate = $currentDate->sub(new \DateInterval(sprintf("PT%dM", $timeOutValue)));
        $timeOutPhpFormat = $timeOutDate->format(FrameworkDateTime::DATETIME_PHP_FORMAT);

        $orders = $this->_orderCollectionFactory->create();

        $orders
            ->addAttributeToFilter('created_at', ['to' => $timeOutPhpFormat])
            ->addFieldToFilter('state',['in' => [Data::INITIALIZED_PAYMENT_ORDER_STATE_VALUE]])
        ;

        $orders->join(
            'sales_order_payment',
            '(main_table.entity_id = sales_order_payment.parent_id)',
            ['method']
        );

        $orders->addFieldToFilter('method',['in' => $this->_groupSpecification->getGroupMethods()]);

        /** @var Order $order */
        foreach ($orders as $order) {
            if ($order->canCancel()) {
                try {
                    $order->registerCancellation(__('Canceled by payment timeout.'));
                    $this->_orderRepository->save($order);
                } catch (\Exception $e) {}
            }
        }
        return true;
    }
}
