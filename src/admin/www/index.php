<?php

define('PATH_SOURCE_DIR', 'admin');

require '../../../config.inc.php';

if (isset($_GET['area']) && (stripos($_GET['area'], ".") !== false || stripos($_GET['area'], "/") !== false)) {
    HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_403));
    exit;
}

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

    $controllerName = 'main';

    if ($request->hasGetVar('area') && is_readable(PATH_CONTROLLERS . $request->getGetVar('area') . EXT_CLASS)) {
        $controllerName = $request->getGetVar('area');
    } elseif ($request->hasGetVar('area')) {
        HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_404));
        exit;
    }

    $controller = new $controllerName;
    
    if ($controller instanceof GuestController && SecurityManager::isAuth()) {
        $model = Model::create();
        $view = RedirectView::create(PATH_WEB);
    } elseif ($controller instanceof UserController && !$controller instanceof baseAjax && !SecurityManager::isAuth()) {
        if ($request->hasGetVar('return')) {
            $curl = $request->getGetVar('return');
        } elseif ($request->hasServerVar('REQUEST_URI')) {
            $curl = base64_encode(PATH_WEB . substr($request->getServerVar('REQUEST_URI'), 1));
        } else {
            $curl = base64_encode(PATH_WEB);
        }
        $model = Model::create();
        $view = RedirectView::create(CommonUtils::makeUrl('userRegister', array('action' => userRegister::ACTION_LOGIN, 'return' => $curl, 'needAuth' => 1)));
    } else {
	$modelAndView = $controller->handleRequest($request);

	$view = $modelAndView->getView();
	$model = $modelAndView->getModel();
    }

    $prefix = PATH_WEB.'?area=';

    if (!$view)
        $view = $controllerName;
    elseif (is_string($view) && $view == 'error')
        $view = new RedirectView($prefix);
    elseif ($view instanceof RedirectToView)
        $view->setPrefix($prefix);

    if (!$view instanceof View) {
        $viewName = $view;
        $view = PhpViewResolver::create(PATH_TEMPLATES, EXT_TPL)->resolveViewName($viewName);
    }

    if (!$view instanceof RedirectView && !$view instanceof JsonView) {
        $model->set('area', $controllerName);
    }

    $view->render($model);

} catch (Exception $e) {
    if(!headers_sent() && (!defined('__LOCAL_DEBUG__') || __LOCAL_DEBUG__ === false))
        HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_500));

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

    if (defined('__LOCAL_DEBUG__') && (__LOCAL_DEBUG__ === true))
        echo '<pre>'.$msg.'</pre>';
    else {
        mail(BUGLOVERS, $_SERVER['HTTP_HOST'], $msg);
    }
}