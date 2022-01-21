<?php
/**
 *  Get
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    20.10.2021
 * Time:    21:51
 */
namespace HW\QuickPay\Gateway\Http\Client;
use QuickPay\API\Response;

/**
 *
 */
class Get extends AbstractClient{

	/**
	 * @inheritDoc
	 */
	protected function _doRequest($parameters) {
        $paymentId = $parameters['id'];
        $storeId   = $parameters['store_id'];
        $client = $this->_getGatewayClient($storeId);
        return $client->request->get(sprintf('/payments/%s', $paymentId));
	}
}
