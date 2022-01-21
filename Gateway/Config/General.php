<?php
/**
 *  General
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    10.10.2021
 * Time:    20:36
 */
namespace HW\QuickPay\Gateway\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

/**
 *
 */
class General implements \Magento\Payment\Gateway\ConfigInterface {
    const DEFAULT_PATH_PATTERN            = 'payment/%s/%s';

    /** Set of general settings*/
    const IN_COMMON_FIELDS                = [
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

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var string|null
     */
    protected $methodCode;

    /**
     * @var string|null
     */
    protected $pathPattern;
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @param Data                 $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null          $methodCode
     * @param string               $pathPattern
     */
    public function __construct(
        Data                 $helper,
        ScopeConfigInterface $scopeConfig,
                             $methodCode = null,
                             $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->methodCode   = $methodCode;
        $this->pathPattern = $pathPattern;
        $this->_helper = $helper;
    }
    /**
     * @inheritDoc
     */
    public function getValue($field, $storeId = null) {

        if(in_array($field,self::IN_COMMON_FIELDS)){
            $methodCode = DATA::GENERAL_SETTINGS_CODE;
        } else {
            $methodCode = $this->methodCode;
        }

        if ($methodCode === null || $this->pathPattern === null) {
            return null;
        }

        return $this->_scopeConfig->getValue(
            sprintf($this->pathPattern, $methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @inheritDoc
     */
    public function setMethodCode($methodCode) {
        $this->methodCode = $methodCode;
    }

    /**
     * @inheritDoc
     */
    public function setPathPattern($pathPattern) {
        $this->pathPattern = $pathPattern;
    }

    /**
     * @param string   $path
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getConfigValue($path, $storeId = null)  {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE,$storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function isTestMode($storeId = null){
        return $this->_helper->isTestMode($storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getTextOnStatement($storeId = null){
        return $this->_helper->getTextOnStatement($storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getApiKey($storeId = null){
        return $this->_helper->getApiKey($storeId);
    }
    /**
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getPrivateKey($storeId = null){
        return $this->_helper->getPrivateKey($storeId);
    }

    /**
     * Get a setting value
     *
     * @return string
     */
    public function getDefaultLocale($storeId = null) {
        return $this->_helper->getDefaultLocale($storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return string
     */
    public function getBrandingId($storeId = null){
        return $this->_helper->getBrandingId($storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function captureTransactionFee($storeId = null){
        return $this->_helper->captureTransactionFee($storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function isAutoCaptureMode($storeId = null){
        return $this->_helper->isAutoCaptureMode($storeId);
    }

    /**
     * @param null|int|string $storeId
     *
     * @return bool
     */
    public function ifSendOrderConformationEmailByDefaultMagentoLogic($storeId = null){
        return $this->_helper->ifSendOrderConformationEmailByDefaultMagentoLogic($storeId);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function getCallbackUrl($params = []){
        return $this->_helper->getCallbackUrl($params);
    }
    /**
     * @param array $params
     *
     * @return string
     */
    public function getCancelUrl($params = []){
        return $this->_helper->getCancelUrl($params);
    }
    /**
     * @param array $params
     *
     * @return string
     */
    public function getContinueUrl($params = []){
        return $this->_helper->getContinueUrl($params);
    }
}
