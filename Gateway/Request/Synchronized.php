<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Request;

use HW\QuickPay\Gateway\Helper\AmountConverter;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Payment\Method\Specification\Synchronized as SynchronizedSpecification;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\Method\Logger;

class Synchronized extends AbstractRequest
{
    protected SynchronizedSpecification $synchronizedSpecification;

    public function __construct(
        SynchronizedSpecification $synchronizedSpecification,
        SerializerInterface $serializer,
        Data $helper,
        AmountConverter $amountConverter,
        Logger $logger
    ) {
        parent::__construct($serializer, $helper, $amountConverter, $logger);
        $this->synchronizedSpecification = $synchronizedSpecification;
    }

    public function build(array $buildSubject): array
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];
        $payment = $paymentDO->getPayment();

        if ($this->synchronizedSpecification->isSatisfiedBy($payment->getMethod())) {
            return [
                SynchronizedSpecification::SYNCHRONIZED_METHOD_FLAG_CODE => true
            ];
        }

        return [];
    }
}
