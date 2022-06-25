<?php
/**
 *  CancelOrders
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    24.12.2021
 * Time:    11:22
 */
namespace HW\QuickPay\Cron;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Payment\Method\Specification\Group;
use Magento\Framework\Stdlib\DateTime as FrameworkDateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 *
 */
class CancelOrders {

    CONST MIN_TIMEOUT_VALUE = 5;

    /**
     * @var Data
     */
    protected $_helper;
    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;
    /**
     * @var OrderRepository
     */
    protected $_orderRepository;
    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;
    /**
     * @var Group
     */
    protected $_groupSpecification;


    /**
     * @param OrderRepository            $orderRepository
     * @param Group                      $groupSpecification
     * @param CollectionFactory          $orderCollectionFactory
     * @param TimezoneInterface          $localeDate
     * @param Data                       $helper
     */
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

    /**
     * @return bool|void
     *
     */
    public function execute(){


        $timeOutValue = $this->_helper->getCancelTimeout();
        if(!$timeOutValue){ return true; }
        $timeOutValue = ($timeOutValue < self::MIN_TIMEOUT_VALUE)?self::MIN_TIMEOUT_VALUE:$timeOutValue;

        $currentDate = $this->_localeDate->date(null,null,false); /** @var third param is important. We need GMT date.  */
        $timeOutDate = $currentDate->sub(new \DateInterval(sprintf("PT%dM", $timeOutValue)));
        $timeOutPhpFormat = $timeOutDate->format(FrameworkDateTime::DATETIME_PHP_FORMAT);

        $orders = $this->_orderCollectionFactory->create();

        $orders
            ->addAttributeToFilter('created_at', array('to'=>$timeOutPhpFormat))
            ->addFieldToFilter('state',['in' => [Data::INITIALIZED_PAYMENT_ORDER_STATE_VALUE]])
//            ->addFieldToFilter('created_at',['lteq' => $timeOutPhpFormat])
        ;

        $orders->join(
            'sales_order_payment',
            '(main_table.entity_id = sales_order_payment.parent_id)',
            ['method']
        );

        $orders->addFieldToFilter('method',['in' => $this->_groupSpecification->getGroupMethods()]);

        /** @var Order $order */
        foreach ($orders as $order){
            if($order->canCancel()){
                try {
                    $order->registerCancellation(__('Canceled by payment timeout.'));
                    $this->_orderRepository->save($order);
                } catch (\Exception $e) {}
            }
        }
        return true;
    }
}
