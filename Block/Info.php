<?php
/**
 *  Info
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    10.10.2021
 * Time:    19:58
 */
namespace HW\QuickPay\Block;
use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template as FrontendTemplate;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

/**
 *
 */
class Info extends \Magento\Payment\Block\ConfigurableInfo {
    protected SerializerInterface $_serializer;

    /**
     * @param SerializerInterface $serializer
     * @param Context             $context
     * @param ConfigInterface     $config
     * @param array               $data
     */
    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        ConfigInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->_serializer = $serializer;
    }

    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field) {
        return __($field);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _toHtml() {
        /** first part of condition we use for payments made in old version of module */
        if(!$this->getInfo()->getLastTransId()
            && $this->getInfo()->getAdditionalData()){
            $additionalData = $this->_serializer->unserialize($this->getInfo()->getAdditionalData());

            if(isset($additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE])){
                $blockAdditionalData = $this->getLayout()->getBlock('quickpay.payments.linkToAuthorize');
                if(!$blockAdditionalData){
                    $blockAdditionalData = $this->getLayout()->createBlock(FrontendTemplate::class,'quickpay.payments.linkToAuthorize');
                    $blockAdditionalData->setTemplate('HW_QuickPay::info/default/payment_link.phtml');
                    $this->setChild('linkToAuthorize', $blockAdditionalData);
                }
                $blockAdditionalData->setPaymentLink($additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE]);
            }
        }
        return parent::_toHtml();
    }
}
