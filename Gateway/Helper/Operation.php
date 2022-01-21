<?php
/**
 *  Operation
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    29.11.2021
 * Time:    22:22
 */
namespace HW\QuickPay\Gateway\Helper;
use Magento\Framework\DataObject;
use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
/**
 *
 */
class Operation {
    protected AmountConverter $_amountConverter;

    protected static $errorCodes = [
        '30100',
        '40000',
        '40001',
        '40002',
        '40003',
        '50000',
        '50300'
    ];

    /**
     * @param AmountConverter $amountConver
     */
    public function __construct(AmountConverter $amountConverter){
        $this->_amountConverter = $amountConverter;
    }

    /**
     * @param OperationModelInterface $operation
     *
     * @return bool
     */
    public function isOperationRecurring(DataObject $operation): bool {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_RECURRING;
    }

    /**
     * @param OperationModelInterface $operation
     *
     * @return bool
     */
    public function isOperationAuthorize(DataObject $operation): bool {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_AUTHORIZE;
    }

    /**
     * @param OperationModelInterface $operation
     *
     * @return bool
     */
    public function isOperationCapture(DataObject $operation): bool {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_CAPTURE;
    }

    /**
     * @param OperationModelInterface $operation
     *
     * @return bool
     */
    public function isOperationRefund(DataObject $operation): bool {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_REFUND;
    }

    /**
     * @param OperationModelInterface $operation
     *
     * @return bool
     */
    public function isOperationCancel(DataObject $operation): bool {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_CANCEL;
    }

    /**
     * @param OperationModelInterface $operation
     * @param float|null     $amount
     *
     * @return bool
     */
    public function checkOperationAmount(DataObject $operation, float $amount = null):bool {
        return $amount == null || $this->_amountConverter->convert($amount) == $operation->getAmount();
    }

    /**
     * @param OperationModelInterface $operation
     *
     * @return bool
     */
    public function isStatusCodeApproved(DataObject $operation) {
        return !in_array($operation->getQpStatusCode(),self::$errorCodes);
    }
}
