<?php
/**
 *  ResponseConverter
 *
 * @copyright Copyright © 2021 https://headwayit.com/ HeadWayIt. All rights reserved.
 * @author    Ilya Kushnir ilya.kush@gmail.com
 * Date:    29.11.2021
 * Time:    22:06
 */
namespace HW\QuickPay\Gateway\Helper;
/**
 *
 */
class ResponseConverter {

    /**
     * @param array $array
     *
     * @return ResponseObject
     */
    public function convertArrayToObject(array $array) {
        foreach ($array as $key => $value){
            if(is_array($value)){
                if(in_array($key,['variables','operations','fraud_remarks','branding_config'])){
                    foreach ($value as $subKey => $subValue){
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
