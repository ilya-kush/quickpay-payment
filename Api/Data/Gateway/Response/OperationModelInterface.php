<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Api\Data\Gateway\Response;

interface OperationModelInterface
{

    public const OPERATION_TYPE_AUTHORIZE = 'authorize';
    public const OPERATION_TYPE_RECURRING = 'recurring';
    public const OPERATION_TYPE_CAPTURE   = 'capture';
    public const OPERATION_TYPE_REFUND    = 'refund';
    public const OPERATION_TYPE_CANCEL    = 'cancel';

    /**
     * Payent Id
     * @return int
     */
    public function getId():int;

    /**
     * Type of operation (capture, etc)
     * @return string
     */
    public function getType():string;

    /**
     * Amount (dived 100 to get decimal)
     * @return int
     */
    public function getAmount():int;

    /**
     * If the operation is pending
     * @return bool
     */
    public function getPending():bool;

    /**
     * Acquirer that processed this operation
     * @return string
     */
    public function getAcquirer():string;

    /**
     * Timestamp of creation    ISO-8601
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Timestamp of callback ISO-8601
     * @return string
     */
    public function getCallbackAt():string;

    /**
     * QuickPay status code
     * @return string
     */
    public function getQpStatusCode():string;

    /**
     * QuickPay status msg
     * @return string
     */
    public function getQpStatusMsg():string;

    /**
     * Acquirer status code
     * @return string
     */
    public function getAqStatusCode():string;

    /**
     * Acquirer status message
     * @return string
     */
    public function getAqStatusMsg():string;
    /**
     * Operation callback url
     * @return string
     */
    public function getCallbackUrl():string;

    /**
     * The http response code from the callback operation
     * @return string
     */
    public function getCallbackResponseCode():string;

    /**
     * Did the callback succeed
     *
     * @return bool
     */
    public function getCallbackSuccess():bool;

    /**
     * Callback duration (ms)
     *
     * @return int
     */
    public function getCallbackDuration():int;
    /**
     * 3D Secure status
     * @return string
     */
    public function get3dSecureStatus():string;

    /**
     * Object data getter
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     * It is possible to use keys like a/b/c for access nested array data
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member. If data is the string - it will be explode
     * by new line character and converted to array.
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null);
}
