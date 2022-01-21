<?php
/**
 *  RefundValidator
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    02.12.2021
 * Time:    18:07
 */
namespace HW\QuickPay\Gateway\Validator;
use Magento\Framework\DataObject;
/**
 *
 */
class RefundValidator extends AbstractOperationValidator{

	/**
	 * @inheritDoc
	 */
	protected function _checkOperationCondition(DataObject $operation, array $validationSubject): bool {
        /** Process refund operation */
        $amount = $validationSubject['amount']??null;
        return $this->_operationHelper->isOperationRefund($operation) &&
            $this->_operationHelper->checkOperationAmount($operation,$amount);
	}

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function _getDefaultErrorMsg(){
        return __('Refund operation is not detected.');
    }
}
