<?php
/**
 *
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    06.08.2021
 * Time:    11:17
 */
namespace HW\QuickPay\Api\Data\Gateway\Response;
/**
 * Interface PaymentLinkUrlInterface
 *
 * @package HW\QuickPay
 */
interface PaymentLinkUrlInterface {
    /**
     * Url to payment window for payment link
     *
     * @return string
     */
    public function getUrl(): string;
}
