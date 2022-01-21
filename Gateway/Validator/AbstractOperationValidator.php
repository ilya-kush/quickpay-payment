<?php
/**
 *  AbstractOperationValidator
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    02.12.2021
 * Time:    18:02
 */
namespace HW\QuickPay\Gateway\Validator;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
use HW\QuickPay\Gateway\Helper\Operation;
use HW\QuickPay\Gateway\Helper\ResponseConverter;

/**
 *
 */
abstract class AbstractOperationValidator extends AbstractValidator {

    protected ResponseConverter $_responseConverter;
    protected Operation $_operationHelper;


    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param ResponseConverter      $responseConverter
     * @param Operation              $operationHelper
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ResponseConverter $responseConverter,
        Operation $operationHelper
    ) {
        parent::__construct($resultFactory);
        $this->_responseConverter = $responseConverter;
        $this->_operationHelper   = $operationHelper;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject) {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $responsePayment = $this->_responseConverter->convertArrayToObject($validationSubject['response']);

        $statusMsg = $this->_getDefaultErrorMsg();
        $statusCode = '';
        if(is_array($responsePayment->getOperations())){
            foreach ($responsePayment->getOperations() as $operation){
                /** Process capture operation */
                if($this->_checkOperationCondition($operation,$validationSubject)) {
                    $statusCode = $operation->getQpStatusCode();
                    $statusMsg = $operation->getQpStatusMsg();
                    $statusMsg = sprintf("%s (%s)", $statusMsg, $statusCode);
                    if($this->_operationHelper->isStatusCodeApproved($operation)){
                        return $this->createResult(
                            true,
                            []
                        );
                    }
                }
            }
        }
        return $this->createResult(
            false,
            [$statusMsg],
            []
        );
    }

    /**
     * @return string
     */
    protected function _getDefaultErrorMsg() {
        return '';
    }

    /**
     * @param OperationModelInterface $operation
     * @param array $validationSubject
     *
     * @return bool
     */
    abstract protected function _checkOperationCondition(DataObject $operation, array $validationSubject):bool;
}
