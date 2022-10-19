<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Http\Client;

use HW\QuickPay\Model\Payment\Method\Specification\Synchronized as SynchronizedSpecification;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use QuickPay\API\Response;
use HW\QuickPay\Helper\Data;
use QuickPay\QuickPay as GatewayClientClass;

abstract class AbstractClient implements ClientInterface
{
    public const SYNCHRONIZED_QUERY = '?synchronized';

    protected Data $_helper;
    protected Logger $_logger;

    public function __construct(
        Data            $helper,
        Logger          $logger
    ) {
        $this->_logger = $logger;
        $this->_helper = $helper;
    }

	public function placeRequest(TransferInterface $transferObject)
    {
        $parameters = $transferObject->getBody();
        $response   = [];
        try {
            $clientResponse = $this->_doRequest($parameters);
            $message = sprintf("Http status - %s", $clientResponse->httpStatus());
            $response = $clientResponse->asArray();
        } catch (\Exception $e) {
            $message = __($e->getMessage() ?: 'Sorry, but something went wrong');
            throw new ClientException($message);
        } finally{
            $this->_logger->debug(
                [
                    'class'    => get_class($this),
                    'message'  => $message,
                    'request'  => $transferObject->getBody(),
                    'response' => $response
                ]
            );
        }
        return $response;
	}

    abstract protected function _doRequest(array $parameters): Response;

    protected function _getGatewayClient($storeId = null, string $callbackUrl = null): GatewayClientClass
    {
        $api_key = $this->_helper->getApiKey($storeId);
        $additional_headers = [];
        if ($callbackUrl) {
            $additional_headers[] = sprintf("QuickPay-Callback-Url: %s", $callbackUrl);
        }
        $client  = new GatewayClientClass(":{$api_key}",$additional_headers);
        return $client;
    }

    protected function _isSynchronizedQuery(array $parameters): bool
    {
        if (isset($parameters[SynchronizedSpecification::SYNCHRONIZED_METHOD_FLAG_CODE])) {
            if ($parameters[SynchronizedSpecification::SYNCHRONIZED_METHOD_FLAG_CODE]) {
                return true;
            }
            unset($parameters[SynchronizedSpecification::SYNCHRONIZED_METHOD_FLAG_CODE]);
        }
        return false;
    }
}
