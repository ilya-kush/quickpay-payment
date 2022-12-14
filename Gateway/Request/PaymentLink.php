<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
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

class PaymentLink extends AbstractRequest
{

    protected StoreManagerInterface $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        SerializerInterface $serializer,
        Data $helper,
        AmountConverter $amountConverter,
        Logger $logger
    ) {
        parent::__construct($serializer, $helper, $amountConverter, $logger);
        $this->storeManager = $storeManager;
    }

    public function build(array $buildSubject): array
    {
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
        $storeCode = $this->storeManager->getStore($storeId)->getCode();
        $billingAddress = $order->getBillingAddress();
        $parametersPaymentLink = [
            "amount"          => $this->amountConverter->convert($order->getGrandTotalAmount()),
            "continue_url"    =>
                $this->helper->getContinueUrl(['order' => $order->getOrderIncrementId()], $storeCode),
            "cancel_url"      =>
                $this->helper->getCancelUrl(['order' => $order->getOrderIncrementId()], $storeCode),
            "callback_url"    => $this->helper->getCallbackUrl([], $storeCode),
            "customer_email"  => $billingAddress->getEmail(),
            "auto_capture"    => $this->helper->isAutoCaptureMode($storeId),
            /** todo: manage it depends on Acquirer */
            "payment_methods" =>
                $this->helper->getAllowedMethodsOfGateway($storeId, $payment->getMethod()),
            "branding_id"     => $this->helper->getBrandingId($storeId),
            "language"        => $this->getLanguage($storeId),
            "auto_fee"        => $this->helper->captureTransactionFee($storeId),
            "test_mode"       => $this->helper->isTestMode($storeId)
        ];

        return [
            'payment_link' => $parametersPaymentLink
        ];
    }

    /**
     * We use it only because we need to map both norwegian locales to no
     * @param null|int|string $storeId
     */
    protected function getLanguage($storeId = null): string
    {
        $locale = $this->helper->getDefaultLocale($storeId);
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
