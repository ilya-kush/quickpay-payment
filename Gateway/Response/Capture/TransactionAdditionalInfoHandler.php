<?php
/**
 *  TransactionAdditionalInfoHandler
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    22.10.2021
 * Time:    18:59
 */
namespace HW\QuickPay\Gateway\Response\Capture;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Gateway\Response\AbstractTransactionAdditionalInfoHandler;
/**
 *
 */
class TransactionAdditionalInfoHandler extends AbstractTransactionAdditionalInfoHandler {

    /**
     * @inheritDoc
     */
    protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject) {

        $amount = $handlingSubject['amount']??null;

        if($responsePayment->getOperations()){
            foreach ($responsePayment->getOperations() as $operation){
                /** Process capture operation */
                if($this->_operationHelper->isOperationCapture($operation)
                    && $this->_operationHelper->checkOperationAmount($operation,$amount)
                    && $this->_operationHelper->isStatusCodeApproved($operation))
                {
                    /** @var PaymentDataObjectInterface $paymentDO */
                    $paymentDO = $handlingSubject['payment'];
                    /** @var $payment OrderPayment */
                    $payment = $paymentDO->getPayment();

                    $payment->setTransactionId(sprintf(self::TXN_ID_MASK,$responsePayment->getId(),$operation->getType(),$operation->getId()));
                    $payment->setIsTransactionClosed(false);
                    if($payment->isCaptureFinal($amount)){
                        $payment->setIsTransactionClosed(true);
                    }
                    $this->_setTransactionAdditionalInfoFromOperation($operation,$payment);
                }
            }
        }
    }
}
