<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Http\Client;

use HW\QuickPay\Model\Payment\Method\Specification\Synchronized as SynchronizedSpecification;
use QuickPay\API\Response;

class Refund extends AbstractClient
{
    protected function doRequest(array $parameters): Response
    {
        $paymentId = $parameters['id'];
        $storeId   = $parameters['store_id'];
        unset($parameters['id'], $parameters['store_id']);

        $synchronizedRequest = $this->isSynchronizedQuery($parameters);
        if ($synchronizedRequest) {
            unset($parameters[SynchronizedSpecification::SYNCHRONIZED_METHOD_FLAG_CODE]);
        }

        $client = $this->getGatewayClient($storeId);
        return $client->request->post(
            sprintf(
                '/payments/%s/refund%s',
                $paymentId,
                $synchronizedRequest?self::SYNCHRONIZED_QUERY:''
            ),
            $parameters
        );
    }
}
