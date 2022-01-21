<?php
/**
 *
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    06.08.2021
 * Time:    18:03
 */
namespace HW\QuickPay\Api\Data\Gateway\Response;
/**
 * Interface MetadataModelInterface
 *
 * @package HW\QuickPay
 */
interface MetadataModelInterface {

    /**
     * Types of payment
     */
    const TYPE_CARD     = 'card';
    const TYPE_MOBILE   = 'mobile';
    const TYPE_NIN      = 'nin';

    /**
     * Type (card, mobile, nin)
     * see Types of payment
     *
     * @return string
     */
    public function getType():string;

    /**
     * Origin of this transaction or card. If set, describes where it came from.
     *
     * @return string
     */
    public function getOrigin():string;

    /**
     * Card type only: The card brand
     *
     * @return string
     */
    public function getBrand():string;

    /**
     * Card type only: Card BIN
     *
     * @return string
     */
    public function getBin():string;

    /**
     * Card type only: Corporate status
     *
     * @return bool
     */
    public function getCorporate():bool;

    /**
     * Card type only: The last 4 digits of the card number
     *
     * @return string
     */
    public function getLast4():string;

    /**
     * Card type only: The expiration month
     *
     * @return int
     */
    public function getExpMonth():int;

    /**
     * Card type only: The expiration year (YYYY)
     *
     * @return int
     */
    public function getExpYear():int;

    /**
     * Card type only: The card country in ISO 3166-1 alpha-3
     *
     * @return string
     */
    public function getCountry():string;

    /**
     * Card type only: Verified via 3D-Secure
     *
     * @return string
     */
    public function getIs3dSecure():string;

    /**
     * Card type only: 3-D version or type if v2
     *
     * @return string
     */
    public function get3dSecureType():string;

    /**
     * Card type only: PCI safe hash of card number
     *
     * @return string
     */
    public function getHash():string;

    /**
     * Name of cardholder
     *
     * @return string
     */
    public function getIssuedTo():string;

    /**
     * Mobile type only: The mobile number
     *
     * @return string
     */
    public function getNumber():string;

    /**
     * Customer IP
     *
     * @return string
     */
    public function getCustomerIp():string;

    /**
     * Customer country based on IP geo-data, ISO 3166-1 alpha-2
     *
     * @return string
     */
    public function getCustomerCountry():string;

    /**
     * Fraud report description
     *
     * @return string
     */
    public function getFraudReportDescription():string;

    /**
     * Fraud report description
     *
     * @return string
     */
    public function getFraudReportedAt():string;

    /**
     * Suspected fraud
     *
     * @return bool
     */
    public function getFraudSuspected():bool;

    /**
     * Fraud remarks
     * @return string[]
     */
    public function getFraudRemarks():array;

    /**
     * NIN type only. NIN number
     * @return string
     */
    public function getNinNumber():string;

    /**
     * NIN type only. NIN country code, ISO 3166-1 alpha-3
     * @return string
     */
    public function getNinCountryCode():string;

    /**
     * NIN type only. NIN gender
     * @return string
     */
    public function getNinGender():string;
}
