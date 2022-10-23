<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

class General implements ConfigInterface
{
    public const DEFAULT_PATH_PATTERN = 'payment/%s/%s';

    /** Set of general settings*/
    public const IN_COMMON_FIELDS                = [
        Data::PUBLIC_KEY_XML_CODE,
        Data::PRIVATE_KEY_XML_CODE,
        Data::TESTMODE_XML_CODE,
        Data::TEXT_ON_STATEMENT_XML_CODE,
        Data::SEND_ORDER_EMAIL_XML_CODE,
        Data::BRANDING_ID_XML_CODE,
        Data::AUTOCAPTURE_XML_CODE,
        Data::SEND_INVOICE_EMAIL_XML_CODE,
        Data::TRANSACTION_FEE_XML_CODE,
        'order_place_redirect_url',
        'debug',
        'debugReplaceKeys',
        'privateInfoKeys',
        'paymentInfoKeys'
    ];

    protected ScopeConfigInterface $scopeConfig;
    protected ?string $methodCode;
    protected ?string $pathPattern;
    protected Data $helper;

    public function __construct(
        Data                 $helper,
        ScopeConfigInterface $scopeConfig,
        string               $methodCode = null,
        string               $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->methodCode  = $methodCode;
        $this->pathPattern = $pathPattern;
        $this->helper = $helper;
    }

    /**
     * @param $field
     * @param $storeId
     * @return mixed|null
     */
    public function getValue($field, $storeId = null)
    {
        if (in_array($field, self::IN_COMMON_FIELDS)) {
            $methodCode = DATA::GENERAL_SETTINGS_CODE;
        } else {
            $methodCode = $this->methodCode;
        }

        if ($methodCode === null || $this->pathPattern === null) {
            return null;
        }

        return $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * @param string   $path
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getConfigValue(string $path, int $storeId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null|int|string $storeId
     */
    public function isTestMode($storeId = null): bool
    {
        return $this->helper->isTestMode($storeId);
    }

    /**
     * @param null|int|string $storeId
     */
    public function getTextOnStatement($storeId = null): string
    {
        return $this->helper->getTextOnStatement($storeId);
    }

    /**
     * @param null|int|string $storeId
     */
    public function getApiKey($storeId = null): string
    {
        return $this->helper->getApiKey($storeId);
    }
    /**
     * @param null|int|string $storeId
     */
    public function getPrivateKey($storeId = null): string
    {
        return $this->helper->getPrivateKey($storeId);
    }

    /**
     * @param null|int|string $storeId
     */
    public function getDefaultLocale($storeId = null): string
    {
        return $this->helper->getDefaultLocale($storeId);
    }

    /**
     * @param null|int|string $storeId
     */
    public function getBrandingId($storeId = null): string
    {
        return $this->helper->getBrandingId($storeId);
    }

    /**
     * @param null|int|string $storeId
     */
    public function captureTransactionFee($storeId = null): bool
    {
        return $this->helper->captureTransactionFee($storeId);
    }

    /**
     * @param null|int|string $storeId
     */
    public function isAutoCaptureMode($storeId = null): bool
    {
        return $this->helper->isAutoCaptureMode($storeId);
    }

    /**
     * @param null|int|string $storeId
     */
    public function ifSendOrderConformationEmailByDefaultMagentoLogic($storeId = null): bool
    {
        return $this->helper->ifSendOrderConformationEmailByDefaultMagentoLogic($storeId);
    }

    public function getCallbackUrl(array $params = []): string
    {
        return $this->helper->getCallbackUrl($params);
    }

    public function getCancelUrl(array $params = []): string
    {
        return $this->helper->getCancelUrl($params);
    }

    public function getContinueUrl(array $params = []): string
    {
        return $this->helper->getContinueUrl($params);
    }
}
