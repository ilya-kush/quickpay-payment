<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class StoreId extends AbstractRequest
{
    public function build(array $buildSubject): array
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }
        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        return [
            'store_id' => $payment->getOrder()->getStoreId()
        ];
    }
}
