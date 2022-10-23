<?php
/**
 * @author    Ilya Kushnir ilya.kush@gmail.com
 */
namespace HW\QuickPay\Gateway\Helper;

class ResponseConverter
{

    public function convertArrayToObject(array $array): ResponseObject
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (in_array($key, ['variables', 'operations', 'fraud_remarks', 'branding_config'])) {
                    foreach ($value as $subKey => $subValue) {
                        $array[$key][$subKey] = is_array($subValue)?$this->convertArrayToObject($subValue):$subValue;
                    }
                } else {
                    $array[$key]= $this->convertArrayToObject($value);
                }
            }
        }
        return new ResponseObject($array);
    }
}
