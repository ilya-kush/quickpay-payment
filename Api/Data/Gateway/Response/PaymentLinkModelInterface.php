<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Api\Data\Gateway\Response;

interface PaymentLinkModelInterface extends PaymentLinkUrlInterface {
    /**
     * Url to payment window for this payment link
     * @return string
     */
    public function getUrl(): string;

    /**
     * If true, will capture the transaction after authorize succeeds
     * @return bool
     */
    public function getAutoCapture(): bool;

    /**
     * If true, will add acquirer fee to the amount
     * @return bool
     */
    public function getAutoFee(): bool;

    /**
     * Amount to authorize.
     *
     * @return int
     */
    public function getAmount(): int;

    /**
     * Id of agreement that will be used in the payment window
     * @return int
     */
    public function getAgreementId(): int;

    /**
     * Where cardholder is redirected after success
     * @return string
     */
    public function getContinueUrl():string;

    /**
     * Where cardholder is redirected after cancel
     * @return string
     */
    public function getCancelUrl():string;

    /**
     * Endpoint for a POST callback
     * @return string
     */
    public function getCallbackUrl():string;

    /**
     * Lock to these payment methods
     * @return string
     */
    public function getPaymentMethods():string;
}
