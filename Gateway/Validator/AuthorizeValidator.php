<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Validator;
use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
use Magento\Framework\DataObject;

class AuthorizeValidator extends AbstractOperationValidator
{
    /**
     * @param OperationModelInterface $operation
     */
    protected function _checkOperationCondition(DataObject $operation, array $validationSubject): bool
    {
        /** Process authorize operation */
        $amount = $validationSubject['amount'] ?? null;
        return ($this->_operationHelper->isOperationAuthorize($operation)
                || $this->_operationHelper->isOperationRecurring($operation))
            && $this->_operationHelper->checkOperationAmount($operation,$amount);
	}

    protected function _getDefaultErrorMsg(): string
    {
        return (string) __('Authorize operation is not detected.');
    }
}
