<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Validator;

use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
use Magento\Framework\DataObject;

class RefundValidator extends AbstractOperationValidator
{
    /**
     * @param OperationModelInterface $operation
     */
    protected function checkOperationCondition(DataObject $operation, array $validationSubject): bool
    {
        /** Process refund operation */
        $amount = $validationSubject['amount'] ?? null;
        return $this->operationHelper->isOperationRefund($operation) &&
            $this->operationHelper->checkOperationAmount($operation, $amount);
    }

    protected function getDefaultErrorMsg(): string
    {
        return (string) __('Refund operation is not detected.');
    }
}
