<?php
/**
 *  AbstractTransactionAdditionalInfoHandler
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    26.10.2021
 * Time:    18:20
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransactionModel;
use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
use HW\QuickPay\Gateway\Helper\Operation;
use HW\QuickPay\Gateway\Helper\ResponseConverter;
/**
 *
 */
abstract class AbstractTransactionAdditionalInfoHandler extends AbstractHandler {
    const TXN_ID_MASK_SEPARATOR = '-';
    const TXN_ID_MASK = '%s'.self::TXN_ID_MASK_SEPARATOR.'%s'.self::TXN_ID_MASK_SEPARATOR.'%s';
    protected Operation $_operationHelper;

    /**
     * @param SerializerInterface $serializer
     * @param ResponseConverter   $responseConverter
     * @param Operation           $operationHelper
     */
    public function __construct(
        SerializerInterface $serializer,
        ResponseConverter $responseConverter,
        Operation $operationHelper
    ) {
        parent::__construct($serializer, $responseConverter);
        $this->_operationHelper = $operationHelper;
    }

    /**
     * @param OperationModelInterface $operation
     * @param OrderPayment   $payment
     */
    public function _setTransactionAdditionalInfoFromOperation(DataObject $operation, OrderPayment $payment){

        $rawDetails = (array)$payment->getAdditionalInformation();
        $rawDetails['Acquirer'] = $operation->getAcquirer();
        $rawDetails['Acquirer Status'] = $this->concatenateStatusMsgString($operation,true);
        $rawDetails['QP Status'] = $this->concatenateStatusMsgString($operation);

        $payment->setTransactionAdditionalInfo(PaymentTransactionModel::RAW_DETAILS,$rawDetails);
        $payment->setTransactionAdditionalInfo('type',$operation->getType());
        $payment->setTransactionAdditionalInfo('amount',$operation->getAmount());
        $payment->setTransactionAdditionalInfo('acquirer',$operation->getAcquirer());
        $payment->setTransactionAdditionalInfo('QpStatusCode',$operation->getQpStatusCode());
        $payment->setTransactionAdditionalInfo('QpStatusMsg',$operation->getQpStatusMsg());
        $payment->setTransactionAdditionalInfo('AqStatusCode',$operation->getAqStatusCode());
        $payment->setTransactionAdditionalInfo('AqStatusMsg',$operation->getAqStatusMsg());
        $payment->setTransactionAdditionalInfo('operation_id',$operation->getId());
    }

    /**
     * @param OperationModelInterface $operation
     * @param bool       $acquirerMsg
     *
     * @return string
     */
    public function concatenateStatusMsgString(DataObject $operation, $acquirerMsg = false):string {
        if($acquirerMsg){
            return sprintf("%s - %s", $operation->getAqStatusCode(), $operation->getAqStatusMsg());
        } else {
            return sprintf("%s - %s", $operation->getQpStatusCode(), $operation->getQpStatusMsg());
        }
    }
}
