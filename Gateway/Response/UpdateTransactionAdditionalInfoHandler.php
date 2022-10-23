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
class UpdateTransactionAdditionalInfoHandler extends AbstractTransactionAdditionalInfoHandler
{

    /**
     * @param array $handlingSubject
     * @param array $response
     *
     * @return array|void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $responsePayment = $this->responseConverter->convertArrayToObject($response);
        return $this->processResponsePayment($responsePayment, $handlingSubject);
    }

    /**
     * @param ResponseObject $responsePayment
     * @return array|void
     */
    protected function processResponsePayment(ResponseObject $responsePayment, array $handlingSubject)
    {
        if (!isset($handlingSubject['transactionId'])) {
            return ;
        }

        if ($responsePayment->getOperations()) {
            foreach ($responsePayment->getOperations() as $operation) {
                if ($this->checkOperationIdAndType(
                    $operation,
                    $handlingSubject['transactionId']
                )
                ) {
                    /** @var PaymentDataObjectInterface $paymentDO */
                    $paymentDO = $handlingSubject['payment'];
                    /** @var $payment OrderPayment */
                    $payment = $paymentDO->getPayment();

                    if (!$this->operationHelper->isStatusCodeApproved($operation)) {
                        $payment->setIsTransactionPending(true);
                        $payment->setIsTransactionApproved(false);
                        $payment->addTransactionCommentsToOrder(
                            $handlingSubject['transactionId'],
                            __($operation->getQpStatusMsg())
                        );
                    } else {
                        $order = $payment->getOrder();
                        if ($order->isPaymentReview()) {
                            /** here we make sure other handlers not sent pending  */
                            $this->checkAndSetIsTransactionPendingFalse($payment);

                            /** here we make sure other handlers not rejected approving  */
                            $this->checkAndSetIsTransactionApprovedTrue($payment);
                        }
                    }

                    $this->setTransactionAdditionalInfoFromOperation($operation, $payment);
                    return $payment->getTransactionAdditionalInfo()[PaymentTransactionModel::RAW_DETAILS] ?? void;
                }
            }
        }
    }

    /**
     * It parses according self::TXN_ID_MASK
     * @return string[]
     */
    protected function parseHandlingTransactionId(string $handlingTransactionId): array
    {
        $parsedArray = explode(self::TXN_ID_MASK_SEPARATOR, $handlingTransactionId);

        $result[ConfigProvider::PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE] = $parsedArray[0] ?? '';
        $result['operation_type'] = $parsedArray[1] ?? '';
        $result['operation_id'] = $parsedArray[2] ?? '';

        return $result;
    }

    /**
     * @param OperationModelInterface $operation
     */
    protected function checkOperationIdAndType(DataObject $operation, string $handlingTransactionId): bool
    {
        $parsedRequestedTransactionId = $this->parseHandlingTransactionId($handlingTransactionId);

        if (in_array(
            $operation->getType(),
            [
                OperationModelInterface::OPERATION_TYPE_AUTHORIZE,
                OperationModelInterface::OPERATION_TYPE_RECURRING
            ]
        )) {
            /** Warning! We don't use TXN_ID_MASK for an authorize transaction.
             *  So if 'operation_type' and 'operation_id' are empty it means we handle an authorize transaction.
             */
            return ($parsedRequestedTransactionId['operation_id'] == '')
                && ($parsedRequestedTransactionId['operation_type'] == '')
                && $parsedRequestedTransactionId[ConfigProvider::PAYMENT_ADDITIONAL_DATA_GATEWAY_TRANS_ID_CODE]
                && $this->operationHelper->isStatusCodeApproved($operation);
        }

        /** Here we process transaction that got id by default magento logic. void instead of cancel-%operation_id% */
        if ($parsedRequestedTransactionId['operation_type'] == TransactionInterface::TYPE_VOID) {
            return $operation->getType() == OperationModelInterface::OPERATION_TYPE_CANCEL;
        }

        /** Rest of operations we match by operation id and operation type */
        $isOperationIdValid = false;
        if ($parsedRequestedTransactionId['operation_id']) {
            $isOperationIdValid = ($operation->getId() == $parsedRequestedTransactionId['operation_id']);
        }

        $isOperationTypeValid = false;
        if ($parsedRequestedTransactionId['operation_type']) {
            $isOperationTypeValid = ($operation->getType() == $parsedRequestedTransactionId['operation_type']);
        }

        return $isOperationIdValid && $isOperationTypeValid;
    }
}
