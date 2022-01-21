<?php
/**
 *  Cancel
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    25.10.2021
 * Time:    10:13
 */
namespace HW\QuickPay\Gateway\Http\Client;
use HW\QuickPay\Model\Payment\Method\Specification\Synchronized as SynchronizedSpecification;

/**
 *
 */
class Cancel extends AbstractClient {
    /**
     * @inheritDoc
     */
    protected function _doRequest($parameters) {
        $paymentId = $parameters['id'];
        $storeId   = $parameters['store_id'];

        $synchronizedRequest = $this->_isSynchronizedQuery($parameters);
        if($synchronizedRequest){
            unset($parameters[SynchronizedSpecification::SYNCHRONIZED_METHOD_FLAG_CODE]);
        }

        $client = $this->_getGatewayClient($storeId);
        return $client->request->post(sprintf('/payments/%s/cancel%s', $paymentId,$synchronizedRequest?self::SYNCHRONIZED_QUERY:''));
    }
}
