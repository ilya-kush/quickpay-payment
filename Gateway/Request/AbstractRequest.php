<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Request;

use HW\QuickPay\Gateway\Response\AbstractTransactionAdditionalInfoHandler;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\AmountConverter;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

abstract class AbstractRequest implements BuilderInterface
{
    protected Data $helper;
    protected Logger $logger;
    protected SerializerInterface $serializer;
    protected AmountConverter $amountConverter;

    public function __construct(
        SerializerInterface $serializer,
        Data                $helper,
        AmountConverter     $amountConverter,
        Logger              $logger
    ) {
        $this->helper      = $helper;
        $this->logger      = $logger;
        $this->serializer = $serializer;
        $this->amountConverter = $amountConverter;
    }

    protected function parseLastTransactionId(string $lastTransactionId): string
    {
        $parts = explode(
            AbstractTransactionAdditionalInfoHandler::TXN_ID_MASK_SEPARATOR,
            $lastTransactionId
        );
        return $parts[0];
    }

    protected function getGatewayPaymentId(OrderPayment $payment): string
    {
        $paymentId = '';
        $additionalData = $payment->getAdditionalData();
        if ($additionalData) {
            $additionalData = $this->serializer->unserialize($additionalData);
            $paymentId  = $additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE]??'';
        }
        if (!$paymentId) {
            $paymentId  = $this->parseLastTransactionId($payment->getLastTransId());
        }
        //Support of payment made with old module
        if (!$paymentId) {
            $additionalInfo = $payment->getAdditionalInformation();
            if ($additionalInfo) {
                if (!is_array($additionalInfo)) {
                    $additionalInfo = $this->serializer->unserialize($additionalInfo);
                }
                $paymentId  = $additionalInfo['Transaction ID']??'';
            }
        }
        return $paymentId;
    }
}
