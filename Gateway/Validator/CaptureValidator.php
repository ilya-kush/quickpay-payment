<?php
/**
 *  CaptureValidator
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    29.11.2021
 * Time:    21:59
 */
namespace HW\QuickPay\Gateway\Validator;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
/**
 *
 */
class CaptureValidator extends AbstractOperationValidator {

    /**
     * @param OperationModelInterface $operation
     * @param array $validationSubject
     *
     * @return bool
     */
    protected function _checkOperationCondition(DataObject $operation,array $validationSubject):bool {
        /** Process capture operation */
        $amount = $validationSubject['amount']??null;
        return $this->_operationHelper->isOperationCapture($operation) &&
            $this->_operationHelper->checkOperationAmount($operation,$amount);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function _getDefaultErrorMsg(){
        return __('Capture operation is not detected.');
    }
}
