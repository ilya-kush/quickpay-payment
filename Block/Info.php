<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Block;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template as FrontendTemplate;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Payment\Gateway\ConfigInterface;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

class Info extends ConfigurableInfo
{
    protected SerializerInterface $_serializer;

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
     * @param $field
     * @return \Magento\Framework\Phrase|string
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * @throws LocalizedException
     */
    public function _toHtml(): string
    {
        /** first part of condition we use for payments made in old version of module */
        if (!$this->getInfo()->getLastTransId()
            && $this->getInfo()->getAdditionalData()) {
            $additionalData = $this->_serializer->unserialize($this->getInfo()->getAdditionalData());

            if (isset($additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE])) {
                $blockAdditionalData = $this->getLayout()->getBlock('quickpay.payments.linkToAuthorize');
                if (!$blockAdditionalData) {
                    $blockAdditionalData = $this->getLayout()->createBlock(
                        FrontendTemplate::class,
                        'quickpay.payments.linkToAuthorize'
                    );
                    $blockAdditionalData->setTemplate('HW_QuickPay::info/default/payment_link.phtml');
                    $this->setChild('linkToAuthorize', $blockAdditionalData);
                }
                $blockAdditionalData->setPaymentLink(
                    $additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE]);
            }
        }
        return parent::_toHtml();
    }
}
