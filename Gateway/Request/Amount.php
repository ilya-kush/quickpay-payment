<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Request;

class Amount extends AbstractRequest
{
    public function build(array $buildSubject): array
    {
        if (!isset($buildSubject['amount'])
            || ($buildSubject['amount'] <= 0)
        ) {
            throw new \InvalidArgumentException('Wrong amount');
        }

        $amount = $buildSubject['amount'];
        return [
            'amount' => $this->amountConverter->convert($amount)
        ];
    }
}
