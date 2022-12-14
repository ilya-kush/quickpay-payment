<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Http\Client;

use QuickPay\API\Response;

class Get extends AbstractClient
{
    protected function doRequest(array $parameters): Response
    {
        $paymentId = $parameters['id'];
        $storeId   = $parameters['store_id'];
        $client = $this->getGatewayClient($storeId);
        return $client->request->get(sprintf('/payments/%s', $paymentId));
    }
}
