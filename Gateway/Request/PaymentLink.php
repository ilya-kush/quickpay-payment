<?php
/**
 *  PaymentLink
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    20.10.2021
 * Time:    20:55
 */
namespace HW\QuickPay\Gateway\Request;
use HW\QuickPay\Gateway\Helper\AmountConverter;
use HW\QuickPay\Helper\Data;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Store\Api\StoreManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 *
 */
class PaymentLink extends AbstractRequest {
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param SerializerInterface   $serializer
     * @param Data                  $helper
     * @param AmountConverter       $amountConverter
     * @param Logger                $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer,
        Data $helper,
        AmountConverter $amountConverter,
        Logger $logger
    ) {
        parent::__construct($serializer, $helper, $amountConverter, $logger);
        $this->_storeManager    = $storeManager;
    }

    /**
	 * @inheritDoc
	 */
	public function build(array $buildSubject) {

        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];

        /** @var OrderAdapter $order */
        $order   = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();
        $storeId = $order->getStoreId();
        $storeCode = $this->_storeManager->getStore($storeId)->getCode();
        $billingAddress = $order->getBillingAddress();
        $parametersPaymentLink = [
            "amount"          => $this->_amountConverter->convert($order->getGrandTotalAmount()),
            "continue_url"    => $this->_helper->getContinueUrl(['order' => $order->getOrderIncrementId()],$storeCode),
            "cancel_url"      => $this->_helper->getCancelUrl(['order' => $order->getOrderIncrementId()],$storeCode),
            "callback_url"    => $this->_helper->getCallbackUrl([],$storeCode),
            "customer_email"  => $billingAddress->getEmail(),
            "auto_capture"    => $this->_helper->isAutoCaptureMode($storeId),
            "payment_methods" => $this->_helper->getAllowedMethodsOfGateway($storeId,$payment->getMethod()), /** todo: manage it depends on Acquirer */
            "branding_id"     => $this->_helper->getBrandingId($storeId),
            "language"        => $this->_getLanguage($storeId),
            "auto_fee"        => $this->_helper->captureTransactionFee($storeId),
            "test_mode"       => $this->_helper->isTestMode($storeId)
        ];

        return [
            'payment_link' => $parametersPaymentLink
        ];
	}

    /**
     * We use it only because we need to map both norwegian locales to no
     * @param null|int|string $storeId
     *
     * @return string
     */
    protected function _getLanguage($storeId = null) {
        $locale = $this->_helper->getDefaultLocale($storeId);
        //
        $map = [
            'nb' => 'no',
            'nn' => 'no',
        ];
        $language = explode('_', $locale)[0];

        if (isset($map[$language])) {
            return $map[$language];
        }

        return $language;
    }
}
