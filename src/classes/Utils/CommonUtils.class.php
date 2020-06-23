<?php

class CommonUtils
{    
    private static $urlMapping = 
        array(
            'userRegister'                      => 'user/',
            
            'aclActionList'                     => 'acl-action/',
            'aclContextList'                    => 'acl-context/',
            'aclRightList'                      => 'acl-right/',
            'aclGroupList'                      => 'acl-group/',
            'categoryList'                      => 'category/',
            'newsList'				=> 'news/',
            'userList'                          => 'users/',
            'creditorList'                      => array(PATH_WEB_CREDITOR => 'cabinet/my/', PATH_WEB_ADMIN => 'creditor/'),
            'investorOfferList'                 => array(PATH_WEB_INVESTOR => 'cabinet/my/', PATH_WEB_ADMIN => 'invest-offer/'),
            'creditRequestList'                 => array(PATH_WEB_CREDITOR => 'cabinet/credit-request/', PATH_WEB_BASE => 'cabinet/my/', PATH_WEB_ADMIN => 'credit-request/', PATH_WEB_INVESTOR => 'cabinet/invest-request/'),
            'creditRequestCreditorOfferList'    => array(PATH_WEB_CREDITOR => 'cabinet/credit-request/offer/', PATH_WEB_BASE => 'cabinet/my/offer/', PATH_WEB_ADMIN => 'credit-request/offer/'),
            'controlPanel'                      => 'cabinet/',
            
            'main'                              => ''
        );
    
    /**
     * Массив контроллеров, которые не определятся как редакторы, но являются таковыми
     * @var array
     */
    private static $controllerEditors = 
        array(
            'userRegister'      => 'userRegister'
        );
    
    public static function makeUrl($moduleName, $arrayParams = array(), $domain = PATH_WEB, $encType = PHP_QUERY_RFC3986)
    {
        $isEditor = mb_substr($moduleName, mb_strlen($moduleName) - 6) == 'Editor';
        if ($isEditor) { // Если контроллер определился как редактор, мы же будем подставлять ссылку от списка - значит её и используем
            $mapped = mb_substr($moduleName, 0, mb_strlen($moduleName) - 6) . 'List';
            if (isset(self::$urlMapping[$mapped])) {
                $moduleName = $mapped;
            }
        }
        
        if (isset(self::$urlMapping[$moduleName])) {
            if (is_array(self::$urlMapping[$moduleName])) {
                foreach(self::$urlMapping[$moduleName] as $key => $value) {
                    if (mb_stripos($domain, $key) === 0) {
                        $link = $domain.self::$urlMapping[$moduleName][$key];
                        break;
                    }
                }
            } else {
                $link = $domain.self::$urlMapping[$moduleName];
            }
            
            if ((isset(self::$controllerEditors[$moduleName]) || $isEditor) && isset($arrayParams['action'])) {
                $link .= "{$arrayParams['action']}.html";
                unset($arrayParams['action']);            
            }
            if ($arrayParams) {
                $link .= "?".http_build_query($arrayParams);
            }
        } else {
            switch($moduleName) {

                default:
                    $link = $domain . "index.php?area={$moduleName}" .self::getQueryStringByParametrs($arrayParams, '&', array(), '&', $encType);

            }
        }
        
        return $link;
    }
    
    /**
     * Вырезает из пути на сайте для возврата UTM метки и success отметку
     * @param string $curl URL на сайте
     * @return string
     */
    public static function postProcessCurrentUrl($curl)
    {
        if (mb_stripos($curl, "success=") !== false || mb_stripos($curl, "utm_") !== false) {
            $curl = preg_replace("/(?(?=\\\?success=\d)success=\d&?|&success=\d)/isu", "", $curl);
            $curl = preg_replace("/(?(?=\\\?added=\d)added=\d&?|&added=\d)/isu", "", $curl);
            $curl = preg_replace("/(?(?=\\\?utm_[a-z]+=[a-z0-9 \-]+)utm_[a-z]+=[a-z0-9 \-]+&?|&utm_[a-z]+=[a-z0-9 \-]+)/isu", "", $curl);
            if (mb_substr($curl, mb_strlen($curl) - 1) == '?') {
                $curl = mb_substr($curl, 0, mb_strlen($curl) - 1);
            }            
        }
        
        return $curl;
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