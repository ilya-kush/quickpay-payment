<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Response\HandlerChain;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Response\HandlerChain;
use Magento\Payment\Gateway\Response\HandlerInterface;

class FetchTransactionInformation extends HandlerChain
{
    /**
     * @var HandlerInterface[] | TMap
     */
    private $handlers;

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
        parent::__construct($tmapFactory, $handlers);
    }

    public function handle(array $handlingSubject, array $response): array
    {
        $updateData = [];
        foreach ($this->handlers as $handler) {
            $_handlerData = $handler->handle($handlingSubject, $response)?:[];
            $updateData   = array_merge($updateData, $_handlerData);
        }
        return $updateData;
    }
}
