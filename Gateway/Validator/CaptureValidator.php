<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Validator;

use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;

class CaptureValidator extends AbstractOperationValidator
{
    /**
     * @param OperationModelInterface $operation
     */
    protected function checkOperationCondition(DataObject $operation, array $validationSubject): bool
    {
        /** Process capture operation */
        $amount = $validationSubject['amount'] ?? null;
        return $this->operationHelper->isOperationCapture($operation) &&
            $this->operationHelper->checkOperationAmount($operation, $amount);
    }

    protected function getDefaultErrorMsg(): string
    {
        return (string) __('Capture operation is not detected.');
    }
}
