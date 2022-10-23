<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Model\Ui\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Config\Source\MethodsList;
use Magento\Framework\View\Asset\Repository as AssetRepository;

class ConfigProvider implements ConfigProviderInterface
{
    public const CODE              = 'quickpay_gateway';
    public const CODE_MOBILEPAY    = 'quickpay_mobilepay';
    public const CODE_KLARNA       = 'quickpay_klarna';
    public const CODE_SWISH        = 'quickpay_swish';
    public const CODE_ANYDAY       = 'quickpay_anyday';
    public const CODE_TRUSTLY      = 'quickpay_trustly';
    public const CODE_VIABILL      = 'quickpay_viabill';
    public const CODE_VIPPS        = 'quickpay_vipps';
    public const PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE = 'payment_link';
    public const PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE = 'gateway_trans_id';

    public const LOGO_FILE_PNG_PATTERN = "%s.png";
    public const LOGO_FILE_SVG_PATTERN = "%s.svg";

    protected AssetRepository $assetRepo;
    protected MethodsList $methodsList;
    protected Data $helper;
    protected Session $session;

    public function __construct(
        Session                                  $session,
        Data                                     $helper,
        MethodsList                              $methodsList,
        AssetRepository                          $assetRepo
    ) {
        $this->assetRepo   = $assetRepo;
        $this->methodsList = $methodsList;
        $this->helper     = $helper;
        $this->session = $session;
    }

    public function getConfig(): array
    {
        $configArray = [];
        $storeId = $this->session->getQuote()->getStoreId();
        foreach ($this->methodsList->toOptionArray() as $_method) {
            $configArray['payment'][$_method['value']] = [
                'redirect_url' => $this->helper->getRedirectUrl(),
                'logo' => $this->getMethodLogo($_method['value'], $storeId),
            ];
        }
        return $configArray;
    }

    /**
     * @param null|int|string $storeId
     * @return string[]
     */
    public function getMethodLogo(string $methodCode, $storeId = null): array
    {
        $methodExploded = explode('_', $methodCode);
        $nameFileLogo = [];
        switch ($methodExploded[1]) {
            case 'trustly':
            case 'anyday':
                $nameFileLogo[] = sprintf(self::LOGO_FILE_SVG_PATTERN, $methodExploded[1]);
                break;
            case 'gateway':
                $nameFileLogo = $this->getGatewayCardLogos($storeId);
                break;
            default:
                $nameFileLogo[] = isset($methodExploded[1]) ?
                    sprintf(self::LOGO_FILE_PNG_PATTERN, $methodExploded[1]) :
                    null;
        }

        $logos = [];
        if (!empty($nameFileLogo)) {
            foreach ($nameFileLogo as $_logoFileName) {
                if (!empty($_logoFileName)) {
                    $logos[] = $this->assetRepo->getUrl(
                        sprintf("HW_QuickPay::images/logo/%s", $_logoFileName)
                    );
                }
            }
        }
        return $logos;
    }

    /**
     * @param null|int|string $storeId
     * @return string[]
     */
    public function getGatewayCardLogos($storeId = null): array
    {
        $gatewayLogos = $this->helper->getGatewayCardLogo($storeId);
        $gatewayLogos = explode(',', $gatewayLogos);
        foreach ($gatewayLogos as $key => $_logoFile) {
            $gatewayLogos[$key] = $_logoFile ? sprintf(self::LOGO_FILE_PNG_PATTERN, $_logoFile) : null;
        }
        return $gatewayLogos;
    }
}
