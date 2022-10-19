<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Helper;

use Magento\Tests\NamingConvention\true\float;

class AmountConverter
{
    /**
     * Convert amount for gateway. Amount must be integer, so we multiply value in 100.
     */
    public function convert(float $amount): int
    {
        return (int) ($amount * 100);
    }

    /**
     * Backconvert amount from gateway.
     */
    public function backConvert(int $amount): float
    {
        return (float) ($amount / 100);
    }
}
