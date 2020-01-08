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
                
            case 'aclActionList':
                $link = PATH_WEB . "control-panel/admin/acl-action/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'aclActionEditor':
                $link = PATH_WEB . "control-panel/admin/acl-action/{$arrayParams['action']}.html";
                unset($arrayParams['action']);
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'aclContextList':
                $link = PATH_WEB . "control-panel/admin/acl-context/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'aclContextEditor':
                $link = PATH_WEB . "control-panel/admin/acl-context/{$arrayParams['action']}.html";
                unset($arrayParams['action']);
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'aclRightList':
                $link = PATH_WEB . "control-panel/admin/acl-right/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'aclRightEditor':
                $link = PATH_WEB . "control-panel/admin/acl-right/{$arrayParams['action']}.html";
                unset($arrayParams['action']);
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'aclGroupList':
                $link = PATH_WEB . "control-panel/admin/acl-group/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'aclGroupEditor':
                $link = PATH_WEB . "control-panel/admin/acl-group/{$arrayParams['action']}.html";
                unset($arrayParams['action']);
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'creditorList':
                $link = PATH_WEB . "control-panel/creditor/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'creditorEditor':
                $link = PATH_WEB . "control-panel/creditor/{$arrayParams['action']}.html";
                unset($arrayParams['action']);
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'adminCreditorList':
                $link = PATH_WEB . "control-panel/admin/creditor/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'investorOfferList':
                $link = PATH_WEB . "control-panel/invest-offer/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'investorOfferEditor':
                $link = PATH_WEB . "control-panel/invest-offer/{$arrayParams['action']}.html";
                unset($arrayParams['action']);
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'adminInvestorOfferList':
                $link = PATH_WEB . "control-panel/admin/invest-offer/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'categoryEditor':
                $link = PATH_WEB . "control-panel/admin/category/{$arrayParams['action']}.html";
                unset($arrayParams['action']);
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'adminCategoryList':
                $link = PATH_WEB . "control-panel/admin/category/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'adminUserEditor':
                $link = PATH_WEB . "control-panel/admin/user/{$arrayParams['action']}.html";
                unset($arrayParams['action']);
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
                
            case 'adminUserList':
                $link = PATH_WEB . "control-panel/admin/user/";
                if ($arrayParams) {
                    $link .= "?".http_build_query($arrayParams);
                }
                break;
            
            case 'adminPanel':
                $link = PATH_WEB . "control-panel/admin/".($arrayParams ? "?".http_build_query($arrayParams) : '');
                break;
            
            case 'controlPanel':
                $link = PATH_WEB . "control-panel/".($arrayParams ? "?".http_build_query($arrayParams) : '');
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