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

    const QP_STATUS_CODE_APPROVED                       = '20000';
    const QP_STATUS_CODE_WAITING_APPROVAL               = '20200';
    const QP_STATUS_CODE_3D_SECURE_IS_REQUIRED          = '30100';
    const QP_STATUS_CODE_SCA_IS_REQUIRED                = '30101';
    const QP_STATUS_CODE_REJECTED_BY_ACQUIRER           = '40000';
    const QP_STATUS_CODE_REQUEST_DATA_ERROR             = '40001';
    const QP_STATUS_CODE_AUTHORIZATION_EXPIRED          = '40002';
    const QP_STATUS_CODE_ABORTED                        = '40003';
    const QP_STATUS_CODE_GATEWAY_ERROR                  = '50000';
    const QP_STATUS_CODE_ACQUIRER_COMMUNICATIONS_ERROR  = '50300';

    protected AmountConverter $_amountConverter;

    /**
     * @see https://learn.quickpay.net/tech-talk/appendixes/errors/#errors-and-codes
     * @var string[]
     */
    protected static $statusCodes = [
        self::QP_STATUS_CODE_APPROVED                       => 'Approved',
        self::QP_STATUS_CODE_WAITING_APPROVAL               => 'Waiting approval',
        self::QP_STATUS_CODE_3D_SECURE_IS_REQUIRED          => '3D Secure is required',
        self::QP_STATUS_CODE_SCA_IS_REQUIRED                => '30101',
        self::QP_STATUS_CODE_REJECTED_BY_ACQUIRER           => 'Rejected By Acquirer',
        self::QP_STATUS_CODE_REQUEST_DATA_ERROR             => 'Request Data Error',
        self::QP_STATUS_CODE_AUTHORIZATION_EXPIRED          => 'Authorization expired',
        self::QP_STATUS_CODE_ABORTED                        => 'Aborted',
        self::QP_STATUS_CODE_GATEWAY_ERROR                  => 'Gateway Error',
        self::QP_STATUS_CODE_ACQUIRER_COMMUNICATIONS_ERROR  => 'Communications Error (with Acquirer)'
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
        return $operation->getQpStatusCode() == self::QP_STATUS_CODE_APPROVED;
    }

    /**
     * @param string $statusCode
     *
     * @return string
     */
    public function getStatusMessage(string $statusCode){
        if(isset(self::$statusCodes[$statusCode])){
            return self::$statusCodes[$statusCode];
        }

        return '';
    }
}
