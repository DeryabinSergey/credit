<?php



require '../../../config.inc.php';

try {
    $request = HttpRequest::create()->
        setGet($_GET)->
        setPost($_POST)->
        setCookie($_COOKIE)->
        setServer($_SERVER)->
        setFiles($_FILES)->
        setSession($_SESSION);
    
    SecurityManager::setCodeCookieName(SecurityManager::COOKIE_CODE_NAME);
    SecurityManager::setCookieName(SecurityManager::COOKIE_USER_NAME);
    SecurityManager::initCode($request);
    if (SecurityManager::isAuthEnabled())
        SecurityManager::authUser($request);
    
    if (!SecurityManager::isAuth()) {
        HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_403));
    } else {
        $str = $_SERVER["REQUEST_URI"];

        $fileName = substr($str, strrpos($str, "/")+1);
        $fileName = substr($fileName, 0, strrpos($fileName, "."));
        $len = strlen($fileName);
        if (substr($fileName, $len - 2) == '-t') {
            $fileName = substr($fileName, 0, $len -2);
        }

        $objectName = substr($str, 3, strpos($str, "/", 3) - 3);
        
        if ($objectName == 'credit-request') {
            $image = CreditRequestImage::dao()->getByLogic(Expression::eq('file_name', $fileName));
            if (
                (!$image->getOwner() instanceof CreditRequest && $image->getUserId() != SecurityManager::getUser()->getId()) ||
                ($image->getOwner() instanceof CreditRequest && !$image->getOwner()->checkPermissions(AclAction::VIEW_ACTION))
            ) {
                HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_403));
            }
            
        } else {
            HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_403));
        }
    }
    
} catch(DatabaseException $e) {
    
    HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_403));
    
} catch (Exception $e) {
    
    if(!headers_sent() && (!defined('__LOCAL_DEBUG__') || __LOCAL_DEBUG__ === false))
        HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_403));

    $msg =
        'uri: ' . $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"] . "\n"
        .'class: '.get_class($e)."\n"
        .'code: '.$e->getCode()."\n"
        .'message: '.$e->getMessage()."\n\n"
        .$e->getTraceAsString()."\n"
        ."\n_POST=".var_export($_POST, true)
        ."\n_GET=".var_export($_GET, true)
        .(
                isset($_SERVER['HTTP_REFERER'])
                        ? "\nREFERER=".var_export($_SERVER['HTTP_REFERER'], true)
                        : null
        );

    mail(BUGLOVERS, $_SERVER['HTTP_HOST'], $msg);
    
}