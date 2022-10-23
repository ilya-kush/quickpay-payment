<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface as StoreScopeInterface;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

class Data extends AbstractHelper
{

    public const INITIALIZED_PAYMENT_ORDER_STATE_VALUE = Order::STATE_PENDING_PAYMENT;

    public const REDIRECT_CONTROLLER_PATH        = 'quickpay/payment/redirect';
    public const CALLBACK_CONTROLLER_PATH        = 'quickpay/payment/callback';
    public const CANCEL_CONTROLLER_PATH          = 'quickpay/payment/cancel';
    public const RETURNS_CONTROLLER_PATH         = 'quickpay/payment/returns';

    public const GENERAL_SETTINGS_CODE           = ConfigProvider::CODE;
    public const GENERAL_CONFIG_XML_PATH         = 'payment/%s/%s';
    public const PUBLIC_KEY_XML_CODE             = 'apikey';
    public const PRIVATE_KEY_XML_CODE            = 'private_key';
    public const SEND_ORDER_EMAIL_XML_CODE       = 'send_order_email';
    public const TESTMODE_XML_CODE               = 'testmode';
    public const TEXT_ON_STATEMENT_XML_CODE      = 'text_on_statement';
    public const BRANDING_ID_XML_CODE            = 'branding_id';
    public const AUTOCAPTURE_XML_CODE            = 'autocapture';
    public const SEND_INVOICE_EMAIL_XML_CODE     = 'send_invoice_email';
    public const TRANSACTION_FEE_XML_CODE        = 'transaction_fee';
    public const CCARD_LOGO_XML_CODE             = 'cardlogos';
    public const ALLOWED_PAYMENT_METHODS_XML_CODE  = 'payment_methods';
    public const SPECIFIED_PAYMENT_METHOD_XML_CODE = 'payment_method_specified';
    public const CANCEL_TIMEOUT_XML_CODE           = 'timeout_to_cancel';

    public function getModuleName(): string
    {
        return $this->_getModuleName();
    }

    public function getDefaultLocale($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue('general/locale/code', StoreScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null|int|string $storeId
     */
    public function isOneStepCheckout($storeId = null): bool
    {
        if ($this->scopeConfig->isSetFlag(
            'amasty_checkout/general/enabled',
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        )) {
            return true;
        }
        return false;
    }

    public function getRedirectUrl(array $params = []): string
    {
        return $this->_getUrl(self::REDIRECT_CONTROLLER_PATH, $params);
    }

    public function getCallbackUrl(array $params = [], string $storeCode = ''): string
    {
        if ($storeCode) {
            return sprintf(
                "%s?___store=%s",
                $this->_getUrl(self::CALLBACK_CONTROLLER_PATH, $params),
                $storeCode
            );
        }
        return $this->_getUrl(self::CALLBACK_CONTROLLER_PATH, $params);
    }

    public function getCancelUrl(array $params = [], string $storeCode = ''): string
    {
        if ($storeCode) {
            return sprintf(
                "%s?___store=%s",
                $this->_getUrl(self::CANCEL_CONTROLLER_PATH, $params),
                $storeCode
            );
        }
        return $this->_getUrl(self::CANCEL_CONTROLLER_PATH, $params);
    }

    public function getContinueUrl(array $params = [], string $storeCode = ''): string
    {
        if ($storeCode) {
            return sprintf(
                "%s?___store=%s",
                $this->_getUrl(self::RETURNS_CONTROLLER_PATH, $params),
                $storeCode
            );
        }
        return $this->_getUrl(self::RETURNS_CONTROLLER_PATH, $params);
    }

    /**
     * @param null|int|string $storeId
     */
    public function getApiKey($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            sprintf(self::GENERAL_CONFIG_XML_PATH, self::GENERAL_SETTINGS_CODE, self::PUBLIC_KEY_XML_CODE),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function getPrivateKey($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            sprintf(self::GENERAL_CONFIG_XML_PATH, self::GENERAL_SETTINGS_CODE, self::PRIVATE_KEY_XML_CODE),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function ifSendOrderConformationEmailByDefaultMagentoLogic($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            sprintf(
                self::GENERAL_CONFIG_XML_PATH,
                self::GENERAL_SETTINGS_CODE,
                self::SEND_ORDER_EMAIL_XML_CODE
            ),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function getCancelTimeout($storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            sprintf(
                self::GENERAL_CONFIG_XML_PATH,
                self::GENERAL_SETTINGS_CODE,
                self::CANCEL_TIMEOUT_XML_CODE
            ),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function isTestMode($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            sprintf(self::GENERAL_CONFIG_XML_PATH, self::GENERAL_SETTINGS_CODE, self::TESTMODE_XML_CODE),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function isAutoCaptureMode($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            sprintf(self::GENERAL_CONFIG_XML_PATH, self::GENERAL_SETTINGS_CODE, self::AUTOCAPTURE_XML_CODE),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function ifSendInvoiceEmail($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            sprintf(
                self::GENERAL_CONFIG_XML_PATH,
                self::GENERAL_SETTINGS_CODE,
                self::SEND_INVOICE_EMAIL_XML_CODE
            ),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function captureTransactionFee($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            sprintf(self::GENERAL_CONFIG_XML_PATH, self::GENERAL_SETTINGS_CODE, self::TRANSACTION_FEE_XML_CODE),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function getTextOnStatement($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            sprintf(
                self::GENERAL_CONFIG_XML_PATH,
                self::GENERAL_SETTINGS_CODE,
                self::TEXT_ON_STATEMENT_XML_CODE
            ),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function getBrandingId($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            sprintf(self::GENERAL_CONFIG_XML_PATH, self::GENERAL_SETTINGS_CODE, self::BRANDING_ID_XML_CODE),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null|int|string $storeId
     */
    public function getGatewayCardLogo($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            sprintf(self::GENERAL_CONFIG_XML_PATH, self::GENERAL_SETTINGS_CODE, self::CCARD_LOGO_XML_CODE),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get payment methods
     *
     * @param string|null $storeId
     */
    public function getAllowedMethodsOfGateway(
        $storeId = null,
        string $paymentMethod = self::GENERAL_SETTINGS_CODE
    ): string {
        $methods = (string) $this->scopeConfig->getValue(
            sprintf(self::GENERAL_CONFIG_XML_PATH, $paymentMethod, self::ALLOWED_PAYMENT_METHODS_XML_CODE),
            StoreScopeInterface::SCOPE_STORE,
            $storeId
        );
        /** Get specified payment methods */
        if ($methods === 'specified') {
            $methods = $this->scopeConfig->getValue(
                sprintf(
                    self::GENERAL_CONFIG_XML_PATH,
                    $paymentMethod,
                    self::SPECIFIED_PAYMENT_METHOD_XML_CODE
                ),
                StoreScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $methods;
    }
}
