<?php
/**
 *  UpdateTransactionAdditionalInfoHandler
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    29.11.2021
 * Time:    16:49
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransactionModel;
use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

/**
 *
 */
class UpdateTransactionAdditionalInfoHandler extends AbstractTransactionAdditionalInfoHandler {

    /**
     * @param array $handlingSubject
     * @param array $response
     *
     * @return array|void
     */
    public function handle(array $handlingSubject, array $response) {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $responsePayment = $this->_responseConverter->convertArrayToObject($response);
        return $this->_processResponsePayment($responsePayment, $handlingSubject);
    }

    /**
     * @param ResponseObject $responsePayment
     * @param array          $handlingSubject
     *
     * @return array|void
     *
     */
	protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject) {

        if(!isset($handlingSubject['transactionId'])){
            return ;
        }

        if($responsePayment->getOperations()){
            foreach ($responsePayment->getOperations() as $operation){
                if($this->_checkOperationIdAndType($operation,$handlingSubject['transactionId'])){
                    /** @var PaymentDataObjectInterface $paymentDO */
                    $paymentDO = $handlingSubject['payment'];
                    /** @var $payment OrderPayment */
                    $payment = $paymentDO->getPayment();

                    $this->_setTransactionAdditionalInfoFromOperation($operation,$payment);

                    return $payment->getTransactionAdditionalInfo()[PaymentTransactionModel::RAW_DETAILS]??void;
                }
            }
        }
	}

    /**
     * It parses according self::TXN_ID_MASK
     * @param string $handlingTransactionId
     *
     * @return string[]
     */
    protected function _parseHandlingTransactionId(string $handlingTransactionId):array {
        $parsedArray = explode(self::TXN_ID_MASK_SEPARATOR,$handlingTransactionId);

        $result[ConfigProvider::PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE] = $parsedArray[0]??'';
        $result['operation_type'] = $parsedArray[1]??'';
        $result['operation_id'] = $parsedArray[2]??'';

        return $result;
    }

    /**
     * @param OperationModelInterface $operation
     * @param string     $handlingTransactionId
     *
     * @return bool
     */
    protected function _checkOperationIdAndType(DataObject $operation,string $handlingTransactionId):bool {

        $parsedRequestedTransactionId = $this->_parseHandlingTransactionId($handlingTransactionId);

        if(in_array($operation->getType(),[OperationModelInterface::OPERATION_TYPE_AUTHORIZE, OperationModelInterface::OPERATION_TYPE_RECURRING])  ){
            /** Warning! We don't use TXN_ID_MASK for an authorize transaction.
             *  So if 'operation_type' and 'operation_id' are empty it means we handle an authorize transaction.
             */
            return ($parsedRequestedTransactionId['operation_id'] == '')
                && ($parsedRequestedTransactionId['operation_type'] == '')
                && $parsedRequestedTransactionId[ConfigProvider::PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE]
                && $this->_operationHelper->isStatusCodeApproved($operation);
        }

        /** Here we process transaction that got id by default magento logic. void instead of cancel-%operation_id% */
        if($parsedRequestedTransactionId['operation_type'] == TransactionInterface::TYPE_VOID){
            return $operation->getType() == OperationModelInterface::OPERATION_TYPE_CANCEL;
        }

        /** Rest of operations we match by operation id and operation type */
        $isOperationIdValid = false;
        if($parsedRequestedTransactionId['operation_id']){
            $isOperationIdValid = ($operation->getId() == $parsedRequestedTransactionId['operation_id']);
        }

        $isOperationTypeValid = false;
        if($parsedRequestedTransactionId['operation_type']){
            $isOperationTypeValid = ($operation->getType() == $parsedRequestedTransactionId['operation_type']);
        }

        return $isOperationIdValid && $isOperationTypeValid;
    }
}
