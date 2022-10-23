<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class Response extends AbstractValidator
{

    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response']) || !is_array($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $responseArray = $validationSubject['response'];
        $errors = [];

        // Quickpay sends empty response with thirdpatry payment methods (ex. anyday)
//        if (empty($responseArray) || !is_array($validationSubject['response'])) {
//            $errors[] = 'Empty response.';
//        }

        if (isset($responseArray['message']) || isset($responseArray['errors'])) {
            if (isset($responseArray['message'])) {
                $errors[] = sprintf("%s:", $responseArray['message']);
            }
            if (isset($responseArray['errors']) && is_array($responseArray['errors'])) {
                foreach ($responseArray['errors'] as $_field => $_validationError) {
                    $errors[] = sprintf(' %s - %s.', $_field, implode(',', $_validationError));
                }
            }
        }

        if (empty($errors)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                $errors
            );
        }
    }
}
