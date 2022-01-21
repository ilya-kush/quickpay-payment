<?php
/**
 *  TransactionAdditionalInfoHandler
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    22.10.2021
 * Time:    18:47
 */
namespace HW\QuickPay\Gateway\Response\Authorize;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Gateway\Helper\ResponseObject;
use HW\QuickPay\Gateway\Response\AbstractTransactionAdditionalInfoHandler;
use HW\QuickPay\Model\Ui\Checkout\ConfigProvider;

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
                /** Process authorization operation */
                if(($this->_operationHelper->isOperationAuthorize($operation) || $this->_operationHelper->isOperationRecurring($operation))
                    && $this->_operationHelper->checkOperationAmount($operation,$amount)
                    && $this->_operationHelper->isStatusCodeApproved($operation))
                {
                    /** @var PaymentDataObjectInterface $paymentDO */
                    $paymentDO = $handlingSubject['payment'];
                    /** @var $payment OrderPayment */
                    $payment = $paymentDO->getPayment();

                    $payment->setTransactionId($responsePayment->getId());
                    $payment->setIsTransactionClosed(false);

                    $this->_setTransactionAdditionalInfoFromOperation($operation,$payment);
                    $this->_addPaymentAdditionalData($payment,ConfigProvider::PAYMENT_ADDITIONAL_DATA_REDIRECT_URL_CODE,null);
                }
            }
        }
	}
}
