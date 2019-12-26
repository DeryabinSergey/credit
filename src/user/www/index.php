<?php

require '../../../config.inc.php';

if (isset($_GET['area']) && (stripos($_GET['area'], ".") !== false || stripos($_GET['area'], "/") !== false)) {
    HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_403));
    exit;
}

try {

    session_name('credit');
    Session::start();
    
    $request = HttpRequest::create()->
        setGet($_GET)->
        setPost($_POST)->
        setCookie($_COOKIE)->
        setServer($_SERVER)->
        setFiles($_FILES)->
        setSession($_SESSION);

    $controllerName = 'main';

    if ($request->hasGetVar('area') && is_readable(PATH_CONTROLLERS . $request->getGetVar('area') . EXT_CLASS)) {
        $controllerName = $request->getGetVar('area');
    } elseif ($request->hasGetVar('area')) {
        HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_404));
        exit;
    }
    
    if (!SecurityManager::isAuth() && $controllerName != 'auth') {
        $view = RedirectToView::create('auth');
        $model = Model::create()->set('return', base64_encode(defined('PATH_WEB') ? PATH_WEB . substr($request->getServerVar('REQUEST_URI'), 1) : $request->getServerVar('REQUEST_URI')));
    } else {
        $controller = new $controllerName;
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