<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use HW\QuickPay\Api\Data\Gateway\Response\MetadataModelInterface;
use HW\QuickPay\Api\Data\Gateway\Response\PaymentModelInterface;
use HW\QuickPay\Gateway\Helper\ResponseObject;

class MetaDataHandler extends AbstractHandler
{
	protected function _processResponsePayment(ResponseObject $responsePayment, array $handlingSubject): void
    {
        if ($responsePayment->getType() != PaymentModelInterface::MODEL_TYPE_PAYMENT) {
            return;
        }
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        /** @var $payment OrderPayment */
        $payment = $paymentDO->getPayment();

        //$payment->setAdditionalInformation('Currency', $responsePayment->getCurrency());
        if ($responsePayment->getMetadata()) {
            if ($responsePayment->getMetadata()->getType()) {
                $payment->setAdditionalInformation('Type', $responsePayment->getMetadata()->getType());
                if ($responsePayment->getMetadata()->getType() == MetadataModelInterface::TYPE_CARD) {
                    $payment->setCcType($responsePayment->getMetadata()->getBrand());
                    $payment->setCcLast4(
                        sprintf("%s", $responsePayment->getMetadata()->getLast4())
                    );

                    if ($responsePayment->getMetadata()->getExpMonth()
                        && $responsePayment->getMetadata()->getExpYear()) {
                        $payment->setCcExpMonth($responsePayment->getMetadata()->getExpMonth());
                        $payment->setCcExpYear($responsePayment->getMetadata()->getExpYear());
                        $payment->setAdditionalInformation(
                            'Card Expiration Date',
                            date(
                                'Y-m',
                                strtotime(sprintf(
                                    "%s-%s",
                                    $responsePayment->getMetadata()->getExpYear(),
                                    $responsePayment->getMetadata()->getExpMonth())
                                )
                            )
                        );
                    }

                    $payment->setCcStatusDescription(
                        $responsePayment->getMetadata()->getFraudReportDescription()
                    );
                    $payment->setCcTransId($responsePayment->getId());
                    $payment->setAdditionalInformation('Card Type', $responsePayment->getMetadata()->getBrand());
                    $payment->setAdditionalInformation('Card Number',
                        sprintf("XXXX-%s", $responsePayment->getMetadata()->getLast4())
                    );
                    if ($responsePayment->getMetadata()->getIssuedTo()) {
                        $payment->setCcOwner($responsePayment->getMetadata()->getIssuedTo());
                        $payment->setAdditionalInformation('Card Owner', $responsePayment->getMetadata()->getIssuedTo());
                    }
                } elseif ($responsePayment->getMetadata()->getType() == MetadataModelInterface::TYPE_MOBILE) {
                    $payment->setAdditionalInformation('Phone number', $responsePayment->getMetadata()->getNumber());
                } elseif ($responsePayment->getMetadata()->getType() == MetadataModelInterface::TYPE_NIN) {
                    $payment->setAdditionalInformation('Nin number', $responsePayment->getMetadata()->getNinNumber());
                    $payment->setAdditionalInformation('Nin country', $responsePayment->getMetadata()->getNinCountryCode());
                    $payment->setAdditionalInformation('Nin gender', $responsePayment->getMetadata()->getNinGender());
                }
            }
        }
	}
}
