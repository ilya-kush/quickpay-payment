<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response\Initialize;
use HW\QuickPay\Gateway\Helper\ResponseConverter;
use HW\QuickPay\Gateway\Response\AbstractHandler;
use Magento\Framework\DataObject;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Helper\Data;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;

class OrderState extends AbstractHandler
{
    protected OrderConfig $_orderConfig;

    public function __construct(
        OrderConfig         $orderConfig,
        SerializerInterface $serializer,
        ResponseConverter   $responseConverter
    ) {
        parent::__construct($serializer, $responseConverter);
        $this->_orderConfig = $orderConfig;
    }

	protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject)
    {
        if (!isset($handlingSubject['stateObject'])
            || !$handlingSubject['stateObject'] instanceof DataObject
        ) {
            throw new \InvalidArgumentException('State object should be provided');
        }
        $stateObject = $handlingSubject['stateObject'];
        $stateObject->setData('state', Data::INITIALIZED_PAYMENT_ORDER_STATE_VALUE);
        $stateObject->setData('status',
            $this->_orderConfig->getStateDefaultStatus(Data::INITIALIZED_PAYMENT_ORDER_STATE_VALUE)
        );
        $stateObject->setData('is_notified', 1);
	}
}
