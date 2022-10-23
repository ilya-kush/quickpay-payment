<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class PaymentId extends AbstractRequest
{
    public function build(array $buildSubject): array
    {
        /** It is used in command fetch_transaction_info */
        if (isset($buildSubject['transactionId']) && $buildSubject['transactionId']) {
            return [
                'id' => $this->parseLastTransactionId($buildSubject['transactionId'])
            ];
        }

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];

        /** second part supports payments created by old version module */
        $gatewayPaymentId = $this->getGatewayPaymentId($paymentDO->getPayment());

        return [
            'id' => $gatewayPaymentId
        ];
    }
}
