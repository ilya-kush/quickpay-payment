<?php
/**
 *  AmountConverter
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    29.11.2021
 * Time:    22:26
 */
namespace HW\QuickPay\Gateway\Helper;
/**
 *
 */
class AmountConverter {
    /**
     * Convert amount for gateway. Amount must be integer, so we multiply value in 100.
     *
     * @param float $amount
     *
     * @return int
     */
    public function convert($amount){
        return (int) ($amount * 100);
    }

    /**
     * Backconvert amount from gateway.
     * @param int $amount
     *
     * @return float
     */
    public function backConvert($amount){
        return ($amount / 100);
    }
}
