<?php
/**
 *  InitializationValidator
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    02.12.2021
 * Time:    18:57
 */
namespace HW\QuickPay\Gateway\Validator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use HW\QuickPay\Gateway\Helper\ResponseConverter;

/**
 *
 */
class InitializationValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator {

    protected ResponseConverter $_responseConverter;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ResponseConverter      $responseConverter
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ResponseConverter $responseConverter
    ) {
        parent::__construct($resultFactory);
        $this->_responseConverter = $responseConverter;
    }

	/**
	 * @inheritDoc
	 */
	public function validate(array $validationSubject) {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $responsePayment = $this->_responseConverter->convertArrayToObject($validationSubject['response']);
        $errors = [];

        if(!$responsePayment->getId()){
            $errors[] = __('There is no transaction ID.');
        }

        if(!($responsePayment->getLink() && $responsePayment->getLink()->getUrl())){
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
