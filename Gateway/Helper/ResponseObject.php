<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Helper;

use HW\QuickPay\Api\Data\Gateway\Response\PaymentModelInterface;
use Magento\Framework\DataObject;

class ResponseObject extends DataObject implements PaymentModelInterface
{
    /**
     * Converts field names for setters and getters (but ignore digits. only capital letters)
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unnecessary preg_replace
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        //$result = strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $name), '_'));
        $result = strtolower(trim(preg_replace('/([A-Z]+)/', "_$1", $name), '_'));
        self::$_underscoreCache[$name] = $result;
        return $result;
    }

    public function getId()
    {
        return $this->_getData('id');
    }

    public function getMerchantId()
    {
        return $this->_getData('merchant_id');
    }

    public function getOrderId()
    {
        return $this->_getData('order_id');
    }

    public function getAccepted()
    {
        return $this->_getData('accepted');
    }

    public function getType()
    {
        return $this->_getData('type');
    }

    public function getTextOnStatement()
    {
        return $this->_getData('text_on_statement');
    }

    public function getState()
    {
        return $this->_getData('state');
    }

    public function getTestMode()
    {
        return $this->_getData('test_mode');
    }

    public function getLink()
    {
        return $this->_getData('link');
    }

    public function getCreatedAt()
    {
        return $this->_getData('created_at');
    }

    public function getUpdatedAt()
    {
        return $this->_getData('updated_at');
    }

    public function getRetentedAt()
    {
        return $this->_getData('retented_at');
    }

    public function getDeadlineAt()
    {
        return $this->_getData('deadline_at');
    }

    public function getBalance()
    {
        return $this->_getData('balance');
    }

    public function getFee()
    {
        return $this->_getData('fee');
    }

    public function getSubscriptionId()
    {
        return $this->_getData('subscription_id');
    }

    public function getOperations()
    {
        return $this->_getData('operations');
    }

    public function getFacilitator()
    {
        return $this->_getData('facilitator');
    }

    public function getAcquirer()
    {
        return $this->_getData('acquirer');
    }

    public function getCurrency()
    {
        return $this->_getData('currency');
    }

    public function getMetadata()
    {
        return $this->_getData('metadata');
    }

    public function getShipping()
    {
        return $this->_getData('shipping');
    }
}
