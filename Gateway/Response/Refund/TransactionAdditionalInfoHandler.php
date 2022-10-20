<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response\Refund;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Gateway\Response\AbstractTransactionAdditionalInfoHandler;

class TransactionAdditionalInfoHandler extends AbstractTransactionAdditionalInfoHandler
{
    protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject): void
    {
        $amount = $handlingSubject['amount']??null;

        if ($responsePayment->getOperations()) {
            foreach ($responsePayment->getOperations() as $operation) {
                /** Process capture operation */
                if ($this->_operationHelper->isOperationRefund($operation)
                    && $this->_operationHelper->checkOperationAmount($operation,$amount)
                    && $this->_operationHelper->isStatusCodeApproved($operation)
                ) {
                    /** @var PaymentDataObjectInterface $paymentDO */
                    $paymentDO = $handlingSubject['payment'];
                    /** @var $payment OrderPayment */
                    $payment = $paymentDO->getPayment();

                    $payment->setTransactionId(sprintf(
                        self::TXN_ID_MASK,
                        $responsePayment->getId(),
                        $operation->getType(),
                        $operation->getId()
                    ));
                    $payment->setIsTransactionClosed(true);
                    $this->_setTransactionAdditionalInfoFromOperation($operation,$payment);
                }
            }
        }
    }
}
