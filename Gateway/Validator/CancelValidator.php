<?php
/**
 *  CancelValidator
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    02.12.2021
 * Time:    18:20
 */
namespace HW\QuickPay\Gateway\Validator;
use Magento\Framework\DataObject;
/**
 *
 */
class CancelValidator extends AbstractOperationValidator {

	/**
	 * @inheritDoc
	 */
	protected function _checkOperationCondition(DataObject $operation, array $validationSubject): bool {
        /** Process cancel operation */
        return $this->_operationHelper->isOperationCancel($operation);
	}

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function _getDefaultErrorMsg(){
        return __('Cancel operation is not detected.');
    }
}
