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
    protected function checkOperationCondition(DataObject $operation, array $validationSubject): bool
    {
        /** Process authorize operation */
        $amount = $validationSubject['amount'] ?? null;
        return ($this->operationHelper->isOperationAuthorize($operation)
                || $this->operationHelper->isOperationRecurring($operation))
            && $this->operationHelper->checkOperationAmount($operation, $amount);
    }

    protected function getDefaultErrorMsg(): string
    {
        return (string) __('Authorize operation is not detected.');
    }
}
