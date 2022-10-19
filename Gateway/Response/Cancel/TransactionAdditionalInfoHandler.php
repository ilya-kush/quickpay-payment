<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response\Cancel;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Gateway\Response\AbstractTransactionAdditionalInfoHandler;

class TransactionAdditionalInfoHandler extends AbstractTransactionAdditionalInfoHandler
{
    protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject): void
    {
        if ($responsePayment->getOperations()) {
            foreach ($responsePayment->getOperations() as $operation) {
                /** Process cancel operation and only approved operation! */
                if ($this->_operationHelper->isOperationCancel($operation)
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
