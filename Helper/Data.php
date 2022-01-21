<?php
/**
 *  Data
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    08.11.2021
 * Time:    13:09
 */
namespace HW\QuickPay\Helper;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

/**
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    const INITIALIZED_PAYMENT_ORDER_STATE_VALUE = Order::STATE_PENDING_PAYMENT;

    const REDIRECT_CONTROLLER_PATH        = 'quickpay/payment/redirect';
    const CALLBACK_CONTROLLER_PATH        = 'quickpay/payment/callback';
    const CANCEL_CONTROLLER_PATH          = 'quickpay/payment/cancel';
    const RETURNS_CONTROLLER_PATH         = 'quickpay/payment/returns';

    const GENERAL_SETTINGS_CODE           = ConfigProvider::CODE;
    const GENERAL_CONFIG_XML_PATH         = 'payment/%s/%s';
    const PUBLIC_KEY_XML_CODE             = 'apikey';
    const PRIVATE_KEY_XML_CODE            = 'private_key';
    const SEND_ORDER_EMAIL_XML_CODE       = 'send_order_email';
    const TESTMODE_XML_CODE               = 'testmode';
    const TEXT_ON_STATEMENT_XML_CODE      = 'text_on_statement';
    const BRANDING_ID_XML_CODE            = 'branding_id';
    const AUTOCAPTURE_XML_CODE            = 'autocapture';
    const SEND_INVOICE_EMAIL_XML_CODE     = 'send_invoice_email';
    const TRANSACTION_FEE_XML_CODE        = 'transaction_fee';
    const CCARD_LOGO_XML_CODE             = 'cardlogos';
    const ALLOWED_PAYMENT_METHODS_XML_CODE  = 'payment_methods';
    const SPECIFIED_PAYMENT_METHOD_XML_CODE = 'payment_method_specified';
    const CANCEL_TIMEOUT_XML_CODE           = 'timeout_to_cancel';

    /**
     * @return string
     */
    public function getModuleName(){
        return $this->_getModuleName();
    }

    /**
     * Get a setting value
     *
     * @return string
     */
    public function getDefaultLocale($storeId = null) {
        return $this->scopeConfig->getValue('general/locale/code',StoreScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null|int|string $storeId
     * @return bool
     */
    public function isOneStepCheckout($storeId = null): bool {
        if($this->scopeConfig->isSetFlag('amasty_checkout/general/enabled',StoreScopeInterface::SCOPE_STORE,$storeId)){
            return true;
        }
        return false;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getRedirectUrl($params = []){
        return $this->_getUrl(self::REDIRECT_CONTROLLER_PATH,$params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getCallbackUrl($params = []){
        return $this->_getUrl(self::CALLBACK_CONTROLLER_PATH,$params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getCancelUrl($params = []){
        return $this->_getUrl(self::CANCEL_CONTROLLER_PATH,$params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getContinueUrl($params = []){
        return $this->_getUrl(self::RETURNS_CONTROLLER_PATH,$params);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getApiKey($storeId = null) {
        return $this->scopeConfig->getValue(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::PUBLIC_KEY_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }
    /**
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getPrivateKey($storeId = null) {
        return $this->scopeConfig->getValue(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::PRIVATE_KEY_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function ifSendOrderConformationEmailByDefaultMagentoLogic($storeId = null){
        return $this->scopeConfig->isSetFlag(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::SEND_ORDER_EMAIL_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return int
     */
    public function getCancelTimeout($storeId = null){
        return (int) $this->scopeConfig->getValue(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::CANCEL_TIMEOUT_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function isTestMode($storeId = null){
        return $this->scopeConfig->isSetFlag(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::TESTMODE_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function isAutoCaptureMode($storeId = null){
        return $this->scopeConfig->isSetFlag(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::AUTOCAPTURE_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function ifSendInvoiceEmail($storeId = null){
        return $this->scopeConfig->isSetFlag(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::SEND_INVOICE_EMAIL_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function captureTransactionFee($storeId = null){
        return $this->scopeConfig->isSetFlag(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::TRANSACTION_FEE_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getTextOnStatement($storeId = null){
        return $this->scopeConfig->getValue(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::TEXT_ON_STATEMENT_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getBrandingId($storeId = null){
        return $this->scopeConfig->getValue(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::BRANDING_ID_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getGatewayCardLogo($storeId = null){
        return $this->scopeConfig->getValue(sprintf(self::GENERAL_CONFIG_XML_PATH,self::GENERAL_SETTINGS_CODE,self::CCARD_LOGO_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * Get payment methods
     *
     * @param string|null $storeId
     * @param string|null $paymentMethod
     *
     * @return string
     */
    public function getAllowedMethodsOfGateway($storeId = null,$paymentMethod = self::GENERAL_SETTINGS_CODE) {
        $methods = $this->scopeConfig->getValue(sprintf(self::GENERAL_CONFIG_XML_PATH,$paymentMethod,self::ALLOWED_PAYMENT_METHODS_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
        /** Get specified payment methods */
        if ($methods === 'specified') {
            $methods = $this->scopeConfig->getValue(sprintf(self::GENERAL_CONFIG_XML_PATH,$paymentMethod,self::SPECIFIED_PAYMENT_METHOD_XML_CODE), StoreScopeInterface::SCOPE_STORE,$storeId);
        }
        return $methods;
    }
}


