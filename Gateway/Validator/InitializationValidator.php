<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use HW\QuickPay\Gateway\Helper\ResponseConverter;

class InitializationValidator extends AbstractValidator
{
    protected ResponseConverter $responseConverter;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ResponseConverter $responseConverter
    ) {
        parent::__construct($resultFactory);
        $this->responseConverter = $responseConverter;
    }

    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $responsePayment = $this->responseConverter->convertArrayToObject($validationSubject['response']);
        $errors = [];

        if (!$responsePayment->getId()) {
            $errors[] = __('There is no transaction ID.');
        }

        if (!($responsePayment->getLink() && $responsePayment->getLink()->getUrl())) {
            $errors[] = __('There is no link to payment gateway.');
        }

        if (empty($errors)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                $errors
            );
        }
    }
}
