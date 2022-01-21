<?php
/**
 *  AuthorizeValidator
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    02.12.2021
 * Time:    18:29
 */
namespace HW\QuickPay\Gateway\Validator;
use Magento\Framework\DataObject;
/**
 *
 */
class AuthorizeValidator extends AbstractOperationValidator {

	/**
	 * @inheritDoc
	 */
	protected function _checkOperationCondition(DataObject $operation, array $validationSubject): bool {
        /** Process authorize operation */
        $amount = $validationSubject['amount']??null;
        return ($this->_operationHelper->isOperationAuthorize($operation) || $this->_operationHelper->isOperationRecurring($operation))
            && $this->_operationHelper->checkOperationAmount($operation,$amount);
	}

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function _getDefaultErrorMsg(){
        return __('Authorize operation is not detected.');
    }
}
