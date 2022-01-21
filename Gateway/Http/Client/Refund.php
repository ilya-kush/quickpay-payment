<?php
/**
 *  Refund
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    22.10.2021
 * Time:    20:31
 */
namespace HW\QuickPay\Gateway\Http\Client;
use HW\QuickPay\Model\Payment\Method\Specification\Synchronized as SynchronizedSpecification;
use QuickPay\API\Response;
/**
 *
 */
class Refund extends AbstractClient {
	/**
	 * @inheritDoc
	 */
	protected function _doRequest($parameters) {
        $paymentId = $parameters['id'];
        $storeId   = $parameters['store_id'];
        unset($parameters['id'],$parameters['store_id']);

        $synchronizedRequest = $this->_isSynchronizedQuery($parameters);
        if($synchronizedRequest){
            unset($parameters[SynchronizedSpecification::SYNCHRONIZED_METHOD_FLAG_CODE]);
        }

        $client = $this->_getGatewayClient($storeId);
        return $client->request->post(sprintf('/payments/%s/refund%s', $paymentId,$synchronizedRequest?self::SYNCHRONIZED_QUERY:''),$parameters);
	}
}
