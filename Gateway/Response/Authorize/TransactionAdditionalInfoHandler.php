<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response\Authorize;

use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Gateway\Response\AbstractTransactionAdditionalInfoHandler;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

class TransactionAdditionalInfoHandler extends AbstractTransactionAdditionalInfoHandler
{
    protected function processResponsePayment(ResponseObject $responsePayment, array $handlingSubject): void
    {
        $amount = $handlingSubject['amount']??null;

        if ($responsePayment->getOperations()) {
            $_authorizeOperations = [];
            foreach ($responsePayment->getOperations() as $_operation) {
                /** Process authorization operation */
                if (($this->operationHelper->isOperationAuthorize($_operation)
                        || $this->operationHelper->isOperationRecurring($_operation))
                    && $this->operationHelper->checkOperationAmount($_operation, $amount)
                ) {
                    $_authorizeOperations[$_operation->getId()] = $_operation;
                }
            }

            /** we process only last authorize operation. */
            /** @var OperationModelInterface $operation */
            $operation = end($_authorizeOperations);

            /** @var PaymentDataObjectInterface $paymentDO */
            $paymentDO = $handlingSubject['payment'];
            /** @var $payment OrderPayment */
            $payment = $paymentDO->getPayment();

            $payment->setTransactionId($responsePayment->getId());
            $payment->setIsTransactionClosed(false);

            $this->setTransactionAdditionalInfoFromOperation($operation, $payment);
            $this->addPaymentAdditionalData(
                $payment,
                ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE,
                null
            );

            if (!$this->operationHelper->isStatusCodeApproved($operation)) {
                $payment->setIsTransactionPending(true);
                $payment->setIsTransactionApproved(false);
                $payment->addTransactionCommentsToOrder(
                    $payment->getTransactionId(),
                    __($operation->getQpStatusMsg())
                );
            } else {
                $order = $payment->getOrder();
                if ($order->isPaymentReview()) {
                    /** here we make sure other handlers not sent pending  */
                    if (!$payment->getIsTransactionPending() != true) {
                        $payment->setIsTransactionPending(false);
                    }
                    /** here we make sure other handlers not rejected approving  */
                    if ($payment->getIsTransactionApproved() != false) {
                        $payment->setIsTransactionApproved(true);
                    }
                }
            }
        }
    }
}
