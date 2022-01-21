<?php
/**
 *  AbstractRequest
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    12.10.2021
 * Time:    13:11
 */
namespace HW\QuickPay\Gateway\Request;
use HW\QuickPay\Gateway\Response\AbstractTransactionAdditionalInfoHandler;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\AmountConverter;
use HW\QuickPay\Helper\Data;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;
/**
 *
 */
abstract class AbstractRequest implements BuilderInterface {
    /**
     * @var Data
     */
    protected $_helper;
    /**
     * @var Logger
     */
    protected $_logger;
    /**
     * @var SerializerInterface
     */
    protected $_serializer;
    /**
     * @var AmountConverter
     */
    protected $_amountConverter;

    /**
     * @param SerializerInterface $serializer
     * @param Data                $helper
     * @param AmountConverter     $amountConverter
     * @param Logger              $logger
     */
    public function __construct(
        SerializerInterface $serializer,
        Data                $helper,
        AmountConverter     $amountConverter,
        Logger              $logger
    ) {
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_serializer = $serializer;
        $this->_amountConverter = $amountConverter;
    }

    /**
     *
     * @param string $lastTransactionId
     *
     * @return string
     */
    protected function _parseLastTransactionId($lastTransactionId){
        $parts = explode(AbstractTransactionAdditionalInfoHandler::TXN_ID_MASK_SEPARATOR,$lastTransactionId);
        return $parts[0];
    }

    /**
     * @param OrderPayment $payment
     *
     * @return string
     */
    protected function _getGatewayPaymentId($payment){
        $paymentId = '';
        $additionalData = $payment->getAdditionalData();
        if($additionalData) {
            $additionalData = $this->_serializer->unserialize($additionalData);
            $paymentId  = $additionalData[ConfigProvider::PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE]??'';

        }

        if(!$paymentId){
            $paymentId  = $this->_parseLastTransactionId($payment->getLastTransId());
        }

        //Support of payment made with old module
        if(!$paymentId){
            $additionalInfo = $payment->getAdditionalInformation();
            if($additionalInfo) {
                if(!is_array($additionalInfo)){
                    $additionalInfo = $this->_serializer->unserialize($additionalInfo);
                }
                $paymentId  = $additionalInfo['Transaction ID']??'';
            }
        }

        return $paymentId;
    }

}
