<?php
/**
 *  ErrorMessageMapper
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    02.12.2021
 * Time:    14:16
 */
namespace HW\QuickPay\Gateway\ErrorMapper;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;

/**
 *
 */
class ErrorMessageMapper implements ErrorMessageMapperInterface {

	/**
	 * @inheritDoc
	 */
	public function getMessage(string $code) {
        return __($code);
	}
}
