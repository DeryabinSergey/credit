<?php

setlocale(LC_CTYPE, "ru_RU.utf8");
setlocale(LC_TIME, "ru_RU.utf8");
date_default_timezone_set('Europe/Moscow');

define('DEFAULT_ENCODING', 'UTF-8');
define('DEFAULT_DB_ENCODING', 'UTF8mb4');

mb_internal_encoding(DEFAULT_ENCODING);
mb_regex_encoding(DEFAULT_ENCODING);

define('__LOCAL_DEBUG__', true);

/**
 * Базовый домен - он всегда один во всех проектах и неизменный, не учитывается ничего
 */
$baseDomain = 'd.svdev.ru';

/**
 * Текущий путь с учетом поддоменов
 */
$pathWeb =  $baseDomain;

if (defined('PATH_SOURCE_DIR')) {
    $pathWeb = PATH_SOURCE_DIR.'.'.$pathWeb;
} else {
    define('PATH_SOURCE_DIR', 'user');
}

define('PATH_WEB', 'https://'.$pathWeb.DIRECTORY_SEPARATOR);
define('PATH_WEB_BASE', 'https://'.$baseDomain.DIRECTORY_SEPARATOR);
define('PATH_WEB_ADMIN', 'https://admin.'.$baseDomain.DIRECTORY_SEPARATOR);
define('PATH_WEB_CREDITOR', 'https://credit.'.$baseDomain.DIRECTORY_SEPARATOR);
define('PATH_WEB_INVESTOR', 'https://invest.'.$baseDomain.DIRECTORY_SEPARATOR);

define('COOKIE_DOMAIN', '.'.$baseDomain);

define('PATH_BASE', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PATH_SOURCE', PATH_BASE.'src'.DIRECTORY_SEPARATOR.PATH_SOURCE_DIR.DIRECTORY_SEPARATOR);

define('PATH_CLASSES', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR);
define('PATH_CONTROLLERS', PATH_SOURCE.'controllers'.DIRECTORY_SEPARATOR);
define('PATH_CONTROLLER_COMMANDS', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR);
define('PATH_TEMPLATES', PATH_SOURCE.'templates'.DIRECTORY_SEPARATOR);
define('PATH_MAIL_TEMPLATES', PATH_TEMPLATES.'mail'.DIRECTORY_SEPARATOR);

define('UPLOAD_PATH', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'u'.DIRECTORY_SEPARATOR);
define('UPLOAD_URL', PATH_WEB_BASE . 'u' . DIRECTORY_SEPARATOR);

define('IMAGE_PATH_TEMP', 'temp'.DIRECTORY_SEPARATOR);
define('IMAGE_PATH_TEMP_ORIGINAL', 'temp'.DIRECTORY_SEPARATOR.'o-');

define('IMAGE_PATH_HOTEL_IMAGE', 'hotel-image'.DIRECTORY_SEPARATOR);

define('PREVIEW_PATH_LANDING', 'preview-landing'.DIRECTORY_SEPARATOR);

define('KEYS_PATH', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'misc'.DIRECTORY_SEPARATOR.'keys'.DIRECTORY_SEPARATOR);
define('FONT_PATH', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'misc'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR);
        
define('GOOGLE_RECAPTCHA_OPEN', '6LduXMoUAAAAAFoYraxpvGeUdI_g-Uc5unE9Bq0k');
define('GOOGLE_RECAPTCHA_CLOSED', '6LduXMoUAAAAADH7RjF8_2yJHbT5JTd8wgOOle8m');

define('DEFAULT_EMAIL', 'info@d.svdev.ru');
define('DEFAULT_MAILER', 'Агрегатор Залогов');
define('DEFAULT_FROM', DEFAULT_MAILER.' <'.DEFAULT_EMAIL.'>');

// onPHP init
require '/var/www/onPHP/global.inc.php';

define('DB_NAME', 'credit');

DBPool::me()->setDefault(DB::spawn('MySQLim', 'svd', 'Roswell-47', 'localhost:3306', DB_NAME, false, DEFAULT_DB_ENCODING)->setNeedAutoCommit(true));
        
$autoloader->addPaths(array(
    PATH_CLASSES,
    PATH_CONTROLLERS,
    PATH_BASE.'src'.DIRECTORY_SEPARATOR.'base',

    PATH_CLASSES.'Traits',
    PATH_CLASSES.'Utils',
    PATH_CLASSES.'ViewHelpers',
    PATH_CLASSES.'Interfaces',
    PATH_CLASSES.'Exceptions',
    PATH_CLASSES.'Flow',
    
    PATH_CONTROLLER_COMMANDS.'AclAction',
    PATH_CONTROLLER_COMMANDS.'AclContext',
    PATH_CONTROLLER_COMMANDS.'AclGroup',
    PATH_CONTROLLER_COMMANDS.'AclRight',
    PATH_CONTROLLER_COMMANDS.'Category',
    PATH_CONTROLLER_COMMANDS.'Creditor',
    PATH_CONTROLLER_COMMANDS.'InvestorOffer',
    PATH_CONTROLLER_COMMANDS.'User',

    PATH_CLASSES.'DAOs',
    PATH_CLASSES.'Business',
    PATH_CLASSES.'Proto',
    PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'Business',
    PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'Proto',
    PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'DAOs',
));

define('BUGLOVERS', 'deryabinsergey@gmail.com');

// Расширение для файлов стилей
define('EXT_CSS', '.css');

Cache::setPeer(PeclMemcached::create());
Cache::setDefaultWorker('CacheDaoWorker');
//Cache::setDaoMap(array('CityDAO' => 'NullDaoWorker', 'CountryDAO' => 'NullDaoWorker'));

//PeclMemcached::create()->clean();

session_name('credit');
session_set_cookie_params(0, '/', COOKIE_DOMAIN);

Session::start();