<?php
/**
 *  TransferFactory
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    12.10.2021
 * Time:    13:22
 */
namespace HW\QuickPay\Gateway\Http;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
/**
 *
 */
class TransferFactory implements TransferFactoryInterface {
    /**
     * @var TransferBuilder
     */
    protected $transferBuilder;

    /**
     * @param TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request) {
        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
