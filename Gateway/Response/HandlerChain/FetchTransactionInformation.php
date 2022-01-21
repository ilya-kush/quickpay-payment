<?php
/**
 *  FetchTransactionInformation
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    29.11.2021
 * Time:    16:36
 */
namespace HW\QuickPay\Gateway\Response\HandlerChain;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Response\HandlerChain;
use Magento\Payment\Gateway\Response\HandlerInterface;
/**
 *
 */
class FetchTransactionInformation extends HandlerChain {
    /**
     * @var HandlerInterface[] | TMap
     */
    private $handlers;

    /**
     * @param TMapFactory $tmapFactory
     * @param array $handlers
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $handlers = []
    ) {
        $this->handlers = $tmapFactory->create(
            [
                'array' => $handlers,
                'type' => HandlerInterface::class
            ]
        );
        parent::__construct($tmapFactory,$handlers);
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     *
     * @return array
     */
    public function handle(array $handlingSubject, array $response) {
        $updateData = [];
        foreach ($this->handlers as $handler) {
            $_handlerData = $handler->handle($handlingSubject, $response)?:[];
            $updateData   = array_merge($updateData,$_handlerData);
        }
        return $updateData;
    }
}
