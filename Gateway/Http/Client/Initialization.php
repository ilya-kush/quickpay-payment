<?php
/**
 *  Initialization
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    12.10.2021
 * Time:    11:46
 */
namespace HW\QuickPay\Gateway\Http\Client;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\TransferInterface;
use QuickPay\API\Response;
/**
 *
 */
class Initialization extends AbstractClient {
    /**
     * @param TransferInterface $transferObject
     *
     * @return array
     * @throws ClientException
     */
    public function placeRequest(TransferInterface $transferObject) {

        $parameters = $transferObject->getBody();
        $storeId    = $parameters['store_id'];
        $parametersPayment = $parameters['payment'];
        $response   = [];
        try {
            $client = $this->_getGatewayClient($storeId);
            $clientResponsePayment = $client->request->post('/payments', $parametersPayment);
            $response = $clientResponsePayment->asArray();
            $message = sprintf("Http status - %s", $clientResponsePayment->httpStatus());
            if($clientResponsePayment->isSuccess() && isset($response['id'])){
                $paymentId = $response['id'];
                $parametersPaymentLink = $parameters['payment_link'];
                $clientResponseLink = $client->request->put(sprintf('/payments/%s/link', $paymentId), $parametersPaymentLink);
                $response['link'] = $clientResponseLink->asArray();
                $message = sprintf("Http status - %s", $clientResponseLink->httpStatus());
            }

        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            throw new ClientException($message);
        } finally{
            $this->_logger->debug(
                [
                    'class'    => get_class($this),
                    'message' => $message,
                    'request' => $transferObject->getBody(),
                    'response' => $response
                ]
            );
        }

        return $response;
    }

	/**
	 * @inheritDoc
	 */
	protected function _doRequest($parameters) {
        /** placeholder */
	}
}
