<?php
/**
 *
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    06.08.2021
 * Time:    17:21
 */
namespace HW\QuickPay\Api\Data\Gateway\Response;

/**
 * Interface PaymentModelInterface
 * @see https://learn.quickpay.net/tech-talk/api/services/#POST-payments---format-
 * @package HW\QuickPay
 */
interface PaymentModelInterface {

    const MODEL_TYPE_PAYMENT = 'Payment';

    const STATE_INITIAL    = 'initial';
    const STATE_NEW        = 'new';
    const STATE_PENDING    = 'pending';
    const STATE_REJECTED   = 'rejected';
    const STATE_PROCESSED  = 'processed';
    const STATE_ACTIVE     = 'active';
    const STATE_CANCELED   = 'cancelled';

    /**
     * Payment Id
     *
     * @return int
     */
    public function getId();

    /**
     * Merchant id
     *
     * @return int
     */
    public function getMerchantId();

    /**
     * Order number
     *
     * @return string
     */
    public function getOrderId();

    /**
     * Accepted by acquirer
     *
     * @return bool
     */
    public function getAccepted();

    /**
     * Transaction type
     *
     * @return string
     */
    public function getType();

    /**
     * Text on statement
     *
     * @return string
     */
    public function getTextOnStatement();

    /**
     * State of transaction (initial, pending, new, rejected, processed)
     *
     * @return string
     */
    public function getState();
    /**
     * Test mode
     * is testmode?
     * @return bool
     */
    public function getTestMode();
    /**
     * Payment Link
     *
     * @return PaymentLinkModelInterface
     */
    public function getLink();

    /**
     * Timestamp of creation	ISO-8601
     * @return string
     */
    public function getCreatedAt();

    /**
     * Timestamp of last updated ISO-8601
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Timestamp of retention	ISO-8601
     * @return string
     */
    public function getRetentedAt();

    /**
     * Authorize deadline	ISO-8601
     * @return string
     */
    public function getDeadlineAt();

    /**
     * Balance
     *
     * @return int
     */
    public function getBalance();

    /**
     * Fee added to authorization amount (only relevant on auto-fee)
     *
     * @return int
     */
    public function getFee();

    /**
     * Parent subscription id (only recurring)
     *
     * @return int
     */
    public function getSubscriptionId();

    /**
     * Operations
     *
     * @return OperationModelInterface[]
     */
    public function getOperations();

    /**
     * Facilitator that facilitated the transaction
     * @return string
     */
    public function getFacilitator();

    /**
     * Acquirer that processed the transaction
     * @return string
     */
    public function getAcquirer();

    /**
     * Currency
     *
     * @return string
     */
    public function getCurrency();

    /**
     * Metadata
     * @return MetadataModelInterface
     */
    public function getMetadata();

    /**
     * Shipping Data
     *
     * @return ShippingModelInterface
     */
    public function getShipping();

}
