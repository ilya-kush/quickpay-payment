<?php
/**
 *  FraudHandler
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    15.11.2021
 * Time:    16:48
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Api\Data\Gateway\Response\PaymentModelInterface;
use HW\QuickPay\Gateway\Helper\ResponseObject;
/**
 *
 */
class FraudHandler extends AbstractHandler{
	/**
	 * @inheritDoc
	 */
	protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject) {

        if($responsePayment->getType() != PaymentModelInterface::MODEL_TYPE_PAYMENT){
            return;
        }

        if($responsePayment->getMetadata()) {

            /** @var PaymentDataObjectInterface $paymentDO */
            $paymentDO = $handlingSubject['payment'];
            /** @var $payment OrderPayment */
            $payment = $paymentDO->getPayment();

            /** Unfortunately we should avoid fraud suspect, because of "PSD2 - payments" rule */
            if ($responsePayment->getMetadata()->getFraudSuspected() && 0){

                $payment->setIsTransactionPending(true);
                $payment->setIsFraudDetected(true);
                $payment->setAdditionalInformation('Fraud',$responsePayment->getMetadata()->getFraudReportDescription());
                $payment->setAdditionalInformation('Fraud report',implode(',',$responsePayment->getMetadata()->getFraudRemarks()));
            } else {
                $order = $payment->getOrder();
                if($order->isPaymentReview()){
                    $payment->setIsTransactionPending(false);
                    $payment->setIsTransactionApproved(true);
                    if ($order->isFraudDetected()) {
                        $payment->setIsFraudDetected(false);
                        if ($responsePayment->getMetadata()->getFraudReportDescription()) {
                            $payment->setAdditionalInformation('Fraud',$responsePayment->getMetadata()->getFraudReportDescription());
                        }
                        if(!empty($responsePayment->getMetadata()->getFraudRemarks())){
                            $payment->setAdditionalInformation('Fraud report',implode(',',$responsePayment->getMetadata()->getFraudRemarks()?:[]));
                        }
                    }
                }
            }
        }
	}
}
