<?php
/**
 *  Synchronized
 *
 * @copyright Copyright © 2022 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    06.01.2022
 * Time:    11:45
 */
namespace HW\QuickPay\Gateway\Request;
use HW\QuickPay\Gateway\Helper\AmountConverter;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Payment\Method\Specification\Synchronized as SynchronizedSpecification;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\Method\Logger;

/**
 *
 */
class Synchronized extends AbstractRequest {
    /**
     * @var SynchronizedSpecification
     */
    protected $_synchronizedSpecification;

    /**
     * @param SynchronizedSpecification $synchronizedSpecification
     * @param SerializerInterface       $serializer
     * @param Data                      $helper
     * @param AmountConverter           $amountConverter
     * @param Logger                    $logger
     */
    public function __construct(
        SynchronizedSpecification $synchronizedSpecification,
        SerializerInterface $serializer,
        Data $helper,
        AmountConverter $amountConverter,
        Logger $logger
    ) {
        parent::__construct($serializer, $helper, $amountConverter, $logger);
        $this->_synchronizedSpecification = $synchronizedSpecification;
    }

    /**
	 * @inheritDoc
	 */
	public function build(array $buildSubject) {

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];
        $payment = $paymentDO->getPayment();

        if($this->_synchronizedSpecification->isSatisfiedBy($payment->getMethod())){
            return [
                SynchronizedSpecification::SYNCHRONIZED_METHOD_FLAG_CODE => true
            ];
        }

        return [];
	}
}
