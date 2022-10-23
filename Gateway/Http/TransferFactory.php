<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransferFactory implements TransferFactoryInterface
{
    protected TransferBuilder $transferBuilder;

    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    public function create(array $request): TransferInterface
    {
        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
