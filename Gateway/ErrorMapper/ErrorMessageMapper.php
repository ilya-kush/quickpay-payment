<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\ErrorMapper;

use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;

class ErrorMessageMapper implements ErrorMessageMapperInterface
{
    public function getMessage(string $code)
    {
        return __($code);
    }
}
