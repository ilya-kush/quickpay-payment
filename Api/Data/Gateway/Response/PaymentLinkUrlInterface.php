<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Api\Data\Gateway\Response;

interface PaymentLinkUrlInterface {
    /**
     * Url to payment window for payment link
     * @return string
     */
    public function getUrl(): string;
}
