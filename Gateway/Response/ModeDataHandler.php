<?php
/**
 *  ModeDataHandler
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    15.10.2021
 * Time:    15:28
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Api\Data\Gateway\Response\PaymentModelInterface;
use HW\QuickPay\Gateway\Helper\ResponseObject;

/**
 *
 */
class ModeDataHandler extends AbstractHandler {

    /**
	 * @inheritDoc
	 */
	protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject) {

        if($responsePayment->getType() != PaymentModelInterface::MODEL_TYPE_PAYMENT){
            return;
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        /** @var $payment OrderPayment */
        $payment = $paymentDO->getPayment();

        if($responsePayment->getTestMode()){
            $payment->setAdditionalInformation('Mode', 'Sandbox transaction!');
        }

        if($responsePayment->getFee() > 0){
            $payment->setAdditionalInformation('Transaction fee', $responsePayment->getFee());
        }

        if($responsePayment->getLink()){
            if($responsePayment->getLink()->getAutoFee()){
                $payment->setAdditionalInformation('Autofee', 'Captured');
            }
            if($responsePayment->getLink()->getAutoCapture()){
                $payment->setAdditionalInformation('Capture mode', 'Auto');
            }
        }
	}
}
