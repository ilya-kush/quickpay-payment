<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Helper;
use Magento\Framework\DataObject;
use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;

class Operation
{
    public const QP_STATUS_CODE_APPROVED                       = '20000';
    public const QP_STATUS_CODE_WAITING_APPROVAL               = '20200';
    public const QP_STATUS_CODE_3D_SECURE_IS_REQUIRED          = '30100';
    public const QP_STATUS_CODE_SCA_IS_REQUIRED                = '30101';
    public const QP_STATUS_CODE_REJECTED_BY_ACQUIRER           = '40000';
    public const QP_STATUS_CODE_REQUEST_DATA_ERROR             = '40001';
    public const QP_STATUS_CODE_AUTHORIZATION_EXPIRED          = '40002';
    public const QP_STATUS_CODE_ABORTED                        = '40003';
    public const QP_STATUS_CODE_GATEWAY_ERROR                  = '50000';
    public const QP_STATUS_CODE_ACQUIRER_COMMUNICATIONS_ERROR  = '50300';

    protected AmountConverter $_amountConverter;

    /**
     * @see https://learn.quickpay.net/tech-talk/appendixes/errors/#errors-and-codes
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

    public function __construct(AmountConverter $amountConverter)
    {
        $this->_amountConverter = $amountConverter;
    }

    /**
     * @param OperationModelInterface $operation
     */
    public function isOperationRecurring(DataObject $operation): bool
    {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_RECURRING;
    }

    /**
     * @param OperationModelInterface $operation
     */
    public function isOperationAuthorize(DataObject $operation): bool
    {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_AUTHORIZE;
    }

    /**
     * @param OperationModelInterface $operation
     */
    public function isOperationCapture(DataObject $operation): bool
    {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_CAPTURE;
    }

    /**
     * @param OperationModelInterface $operation
     */
    public function isOperationRefund(DataObject $operation): bool
    {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_REFUND;
    }

    /**
     * @param OperationModelInterface $operation
     */
    public function isOperationCancel(DataObject $operation): bool
    {
        return $operation->getType() == OperationModelInterface::OPERATION_TYPE_CANCEL;
    }

    /**
     * @param OperationModelInterface $operation
     */
    public function checkOperationAmount(DataObject $operation, float $amount = null):bool
    {
        return $amount == null || $this->_amountConverter->convert($amount) == $operation->getAmount();
    }

    /**
     * @param OperationModelInterface $operation
     */
    public function isStatusCodeApproved(DataObject $operation): bool
    {
        return $operation->getQpStatusCode() == self::QP_STATUS_CODE_APPROVED;
    }

    public function getStatusMessage(string $statusCode): string
    {
        if (isset(self::$statusCodes[$statusCode])) {
            return self::$statusCodes[$statusCode];
        }
        return '';
    }
}
