<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Validator;

use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
use Magento\Framework\DataObject;

class CancelValidator extends AbstractOperationValidator
{
    /**
     * @param OperationModelInterface $operation
     */
    protected function checkOperationCondition(DataObject $operation, array $validationSubject): bool
    {
        /** Process cancel operation */
        return $this->operationHelper->isOperationCancel($operation);
    }

    protected function getDefaultErrorMsg(): string
    {
        return (string) __('Cancel operation is not detected.');
    }
}
