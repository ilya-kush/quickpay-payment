<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Api\Data\Gateway\Response;

/**
 *
 */
interface ShippingModelInterface
{
    /**
     * Delivery price
     * @return int
     */
    public function getAmount():int;

    /**
     * elivery VAT rate
     * @return int
     */
    public function getVatRate():int;

    /**
     * Delivery method
     * @return string
     */
    public function getMethod():string;

    /**
     * Delivery company
     * @return string
     */
    public function getCompany():string;

    /**
     * Tracking number
     * @return string
     */
    public function getTrackingNumber():string;

    /**
     * Link to delivery status page
     * @return string
     */
    public function getTrackingUrl():string;
}
