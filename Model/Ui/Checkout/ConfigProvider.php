<?php
/**
 *  ConfigProvider
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    10.10.2021
 * Time:    20:10
 */
namespace HW\QuickPay\Model\Ui\Checkout;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Config\Source\MethodsList;
use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 *
 */
class ConfigProvider implements ConfigProviderInterface {

    const CODE              = 'quickpay_gateway';
    const CODE_MOBILEPAY    = 'quickpay_mobilepay';
    const CODE_KLARNA       = 'quickpay_klarna';
    const CODE_SWISH        = 'quickpay_swish';
    const CODE_ANYDAY       = 'quickpay_anyday';
    const CODE_TRUSTLY      = 'quickpay_trustly';
    const CODE_VIABILL      = 'quickpay_viabill';
    const CODE_VIPPS        = 'quickpay_vipps';
    const PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE = 'payment_link';
    const PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE = 'gateway_trans_id';

    CONST LOGO_FILE_PNG_PATTERN = "%s.png";
    CONST LOGO_FILE_SVG_PATTERN = "%s.svg";
    /**
     * @var AssetRepository
     */
    protected $_assetRepo;
    /**
     * @var MethodsList
     */
    protected $_methodsList;
    /**
     * @var Data
     */
    protected $_helper;
    /**
     * @var Session
     */
    protected $_session;

    /**
     * @param Session         $session
     * @param Data            $helper
     * @param MethodsList     $methodsList
     * @param AssetRepository $assetRepo
     */
    public function __construct(
        Session                                  $session,
        Data                                     $helper,
        MethodsList                              $methodsList,
        AssetRepository                          $assetRepo
    ){
        $this->_assetRepo   = $assetRepo;
        $this->_methodsList = $methodsList;
        $this->_helper = $helper;
        $this->_session = $session;
    }

	/**
	 * @inheritDoc
	 */
	public function getConfig() {
        $configArray = [];
        $storeId = $this->_session->getQuote()->getStoreId();
        foreach ($this->_methodsList->toOptionArray() as $_method){
            $configArray['payment'][$_method['value']] = [
                'redirect_url' => $this->_helper->getRedirectUrl(),
                'logo' => $this->getMethodLogo($_method['value'],$storeId),
            ];
        }
        return $configArray;
	}

    /**
     * @param string $methodCode
     * @param null|int|string $storeId
     * @return string[]
     */
    public function getMethodLogo(string $methodCode,$storeId = null) {
        $methodExploded = explode('_',$methodCode);
        $nameFileLogo = [];
        switch($methodExploded[1]){
            case 'trustly':
            case 'anyday': $nameFileLogo[] = sprintf(self::LOGO_FILE_SVG_PATTERN, $methodExploded[1]); break;
            case 'gateway': $nameFileLogo = $this->getGatewayCardLogos($storeId); break;
            default: $nameFileLogo[] = isset($methodExploded[1])?sprintf(self::LOGO_FILE_PNG_PATTERN, $methodExploded[1]):null;
        }

        $logos = [];
        if($nameFileLogo && !empty($nameFileLogo)){
            foreach ($nameFileLogo as $_logoFileName) {
                if($_logoFileName && !empty($_logoFileName)){
                    $logos[] = $this->_assetRepo->getUrl(sprintf("HW_QuickPay::images/logo/%s", $_logoFileName));
                }
            }
        }

        return $logos;
    }

    /**
     * @param null|int|string $storeId
     * @return false|string[]
     */
    public function getGatewayCardLogos($storeId = null) {
        $gatewayLogos = $this->_helper->getGatewayCardLogo($storeId);
        $gatewayLogos = explode(',',$gatewayLogos);
        foreach ($gatewayLogos as $key => $_logoFile){
            $gatewayLogos[$key] = $_logoFile?sprintf(self::LOGO_FILE_PNG_PATTERN, $_logoFile):null;
        }
        return $gatewayLogos;
    }
}
