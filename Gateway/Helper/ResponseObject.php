<?php
/**
 *  ResponseObject
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    12.10.2021
 * Time:    13:49
 */
namespace HW\QuickPay\Gateway\Helper;
use HW\QuickPay\Api\Data\Gateway\Response\PaymentModelInterface;

/**
 *
 */
class ResponseObject extends \Magento\Framework\DataObject implements PaymentModelInterface {
    /**
     * Converts field names for setters and getters (but ignore digits. only capital letters)
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unnecessary preg_replace
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name) {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        //$result = strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $name), '_'));
        $result = strtolower(trim(preg_replace('/([A-Z]+)/', "_$1", $name), '_'));
        self::$_underscoreCache[$name] = $result;
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getId() {
        return $this->_getData('id');
    }

    /**
     * @inheritDoc
     */
    public function getMerchantId(){
        return $this->_getData('merchant_id');
    }

    /**
     * @inheritDoc
     */
    public function getOrderId(){
        return $this->_getData('order_id');
    }

    /**
     * @inheritDoc
     */
    public function getAccepted(){
        return $this->_getData('accepted');
    }

    /**
     * @inheritDoc
     */
    public function getType(){
        return $this->_getData('type');
    }

    /**
     * @inheritDoc
     */
    public function getTextOnStatement(){
        return $this->_getData('text_on_statement');
    }

    /**
     * @inheritDoc
     */
    public function getState(){
        return $this->_getData('state');
    }

    /**
     * @inheritDoc
     */
    public function getTestMode(){
        return $this->_getData('test_mode');
    }

    /**
     * @inheritDoc
     */
    public function getLink() {
        return $this->_getData('link');
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(){
        return $this->_getData('created_at');
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(){
        return $this->_getData('updated_at');
    }

    /**
     * @inheritDoc
     */
    public function getRetentedAt(){
        return $this->_getData('retented_at');
    }

    /**
     * @inheritDoc
     */
    public function getDeadlineAt(){
        return $this->_getData('deadline_at');
    }

    /**
     * @inheritDoc
     */
    public function getBalance(){
        return $this->_getData('balance');
    }

    /**
     * @inheritDoc
     */
    public function getFee(){
        return $this->_getData('fee');
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionId(){
        return $this->_getData('subscription_id');
    }

    /**
     * @inheritDoc
     */
    public function getOperations(){
        return $this->_getData('operations');
    }

    /**
     * @inheritDoc
     */
    public function getFacilitator(){
        return $this->_getData('facilitator');
    }

    /**
     * @inheritDoc
     */
    public function getAcquirer(){
        return $this->_getData('acquirer');
    }

    /**
     * @inheritDoc
     */
    public function getCurrency(){
        return $this->_getData('currency');
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(){
        return $this->_getData('metadata');
    }

    /**
     * @inheritDoc
     */
    public function getShipping(){
        return $this->_getData('shipping');
    }
}
