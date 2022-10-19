<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order\Payment as OrderPayment;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransactionModel;
use HW\QuickPay\Api\Data\Gateway\Response\OperationModelInterface;
use HW\QuickPay\Gateway\Helper\Operation;
use HW\QuickPay\Gateway\Helper\ResponseConverter;

abstract class AbstractTransactionAdditionalInfoHandler extends AbstractHandler
{
    public const TXN_ID_MASK_SEPARATOR = '-';
    public const TXN_ID_MASK = '%s'.self::TXN_ID_MASK_SEPARATOR.'%s'.self::TXN_ID_MASK_SEPARATOR.'%s';
    protected Operation $_operationHelper;

    public function __construct(
        SerializerInterface $serializer,
        ResponseConverter $responseConverter,
        Operation $operationHelper
    ) {
        parent::__construct($serializer, $responseConverter);
        $this->_operationHelper = $operationHelper;
    }

    /**
     * @param OperationModelInterface $operation
     */
    public function _setTransactionAdditionalInfoFromOperation(DataObject $operation, OrderPayment $payment): void
    {
        $rawDetails = (array) $payment->getAdditionalInformation();
        $rawDetails['Acquirer'] = $operation->getAcquirer();
        $rawDetails['Acquirer Status'] = $this->concatenateStatusMsgString($operation,true);
        $rawDetails['QP Status'] = $this->concatenateStatusMsgString($operation);

        $payment->setTransactionAdditionalInfo(PaymentTransactionModel::RAW_DETAILS, $rawDetails);
        $payment->setTransactionAdditionalInfo('type', $operation->getType());
        $payment->setTransactionAdditionalInfo('amount', $operation->getAmount());
        $payment->setTransactionAdditionalInfo('acquirer', $operation->getAcquirer());
        $payment->setTransactionAdditionalInfo('QpStatusCode', $operation->getQpStatusCode());
        $payment->setTransactionAdditionalInfo('QpStatusMsg', $operation->getQpStatusMsg());
        $payment->setTransactionAdditionalInfo('AqStatusCode', $operation->getAqStatusCode());
        $payment->setTransactionAdditionalInfo('AqStatusMsg', $operation->getAqStatusMsg());
        $payment->setTransactionAdditionalInfo('operation_id', $operation->getId());
    }

    /**
     * @param OperationModelInterface $operation
     */
    public function concatenateStatusMsgString(DataObject $operation, bool $acquirerMsgFlag = false): string
    {
        if ($acquirerMsgFlag) {
            return sprintf("%s - %s", $operation->getAqStatusCode(), $operation->getAqStatusMsg());
        } else {
            return sprintf("%s - %s", $operation->getQpStatusCode(), $operation->getQpStatusMsg());
        }
    }
}
