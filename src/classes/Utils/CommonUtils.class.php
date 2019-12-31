<?php

class CommonUtils
{    
    public static function makeUrl($moduleName, $arrayParams = array(), $encType = PHP_QUERY_RFC3986)
    {
        switch($moduleName) {
            
            case 'userRegister':
                $link = PATH_WEB . "user/{$arrayParams['action']}.html";
                unset($arrayParams['action']);
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
            
            case 'main':
                $link = PATH_WEB . ($arrayParams ? "?".http_build_query($arrayParams) : '');
                break;
            
            default:
                $link = PATH_WEB . "index.php?area={$moduleName}" .self::getQueryStringByParametrs($arrayParams, '&', array(), '&', $encType);
                
        }
        return $link;
    }

    protected static function getQueryStringByParametrs($params = array(), $prefix = '&', $keyToUse = array(), $separator = '&', $encType = PHP_QUERY_RFC3986)
    {
        Assert::isArray($params);

        $params = array_filter($params, function($value, $key) use($keyToUse) { return $value && (!$keyToUse || in_array($key, $keyToUse)); }, ARRAY_FILTER_USE_BOTH);

        if (count($params)) {
            return $prefix.http_build_query($params, '', $separator, $encType);
        }

        return '';
    }
    
    /**
     * @see http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
     * @return string
     */
    public static function genUuid()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
 
    public static function arrayToString(array $array)
    {
        ksort($array);
        $string = "";
        
        foreach($array as $key => $val) {
            if (is_array($val)) {
                $string .= $key."=".self::arrayToString($val);
            } else {
                $string .= $key."=".$val;
            }
        }
        
        return $string;
    }
	
}

?>