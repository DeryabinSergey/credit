<?php

setlocale(LC_CTYPE, "ru_RU.utf8");
setlocale(LC_TIME, "ru_RU.utf8");
date_default_timezone_set('Europe/Moscow');

define('DEFAULT_ENCODING', 'UTF-8');
define('DEFAULT_DB_ENCODING', 'UTF8mb4');

mb_internal_encoding(DEFAULT_ENCODING);
mb_regex_encoding(DEFAULT_ENCODING);

define('__LOCAL_DEBUG__', true);

$pathWeb = $basePathWeb = $baseDomain = 'd.svdev.ru';




if (defined('PATH_SOURCE_DIR')) {
    if (PATH_SOURCE_DIR == 'app') {
        $pathWeb .= DIRECTORY_SEPARATOR.PATH_SOURCE_DIR;
    } else {
        $pathWeb = PATH_SOURCE_DIR . '.' . $pathWeb;
        $baseDomain = PATH_SOURCE_DIR . '.' . $baseDomain;
    }
}
$pathWeb = 'http://'.$pathWeb.DIRECTORY_SEPARATOR;
define('PATH_WEB', $pathWeb);
define('BASE_DOMAIN', $baseDomain);


define('DEFAULT_EMAIL', 'info@domodedovo.travel');
define('DEFAULT_MAILER', 'CoralTravel Домодедово');

if (!defined('PATH_SOURCE_DIR')) { // defaults to front mode
    define('PATH_SOURCE_DIR', 'user');
}

define('PATH_BASE', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('PATH_SOURCE', PATH_BASE.'src'.DIRECTORY_SEPARATOR.PATH_SOURCE_DIR.DIRECTORY_SEPARATOR);

define('PATH_CLASSES', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR);
define('PATH_CONTROLLERS', PATH_SOURCE.'controllers'.DIRECTORY_SEPARATOR);
define('PATH_CONTROLLER_COMMANDS', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR);
define('PATH_TEMPLATES', PATH_SOURCE.'templates'.DIRECTORY_SEPARATOR);
define('PATH_MAIL_TEMPLATES', PATH_TEMPLATES.'mail'.DIRECTORY_SEPARATOR);

define('UPLOAD_PATH', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'u'.DIRECTORY_SEPARATOR);
define('UPLOAD_URL', 'http://' . $basePathWeb . DIRECTORY_SEPARATOR . 'u' . DIRECTORY_SEPARATOR);

define('IMAGE_PATH_TEMP', 'temp'.DIRECTORY_SEPARATOR);
define('IMAGE_PATH_TEMP_ORIGINAL', 'temp'.DIRECTORY_SEPARATOR.'o-');

define('IMAGE_PATH_HOTEL_IMAGE', 'hotel-image'.DIRECTORY_SEPARATOR);

define('PREVIEW_PATH_LANDING', 'preview-landing'.DIRECTORY_SEPARATOR);
define('PREVIEW_PATH_NEWS', 'preview-news'.DIRECTORY_SEPARATOR);
define('PREVIEW_PATH_LANDING_HOT', 'preview-landing-hot'.DIRECTORY_SEPARATOR);
define('PREVIEW_PATH_HOTEL', 'preview-hotel'.DIRECTORY_SEPARATOR);

define('KEYS_PATH', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'misc'.DIRECTORY_SEPARATOR.'keys'.DIRECTORY_SEPARATOR);
define('FONT_PATH', PATH_BASE.'src'.DIRECTORY_SEPARATOR.'misc'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR);
        
// onPHP init
require '/var/www/onPHP/global.inc.php';

define('DB_NAME', 'credit');

DBPool::me()->setDefault(DB::spawn('MySQLim', 'svd', 'Roswell-47', 'localhost:3306', DB_NAME, false, DEFAULT_DB_ENCODING)->setNeedAutoCommit(true));
        
$autoloader->addPaths(array(
    PATH_CLASSES,
    PATH_CONTROLLERS,
    PATH_SOURCE.'base',

    PATH_CLASSES.'Traits',
    PATH_CLASSES.'Utils',
    PATH_CLASSES.'ViewHelpers',
    PATH_CLASSES.'Interfaces',
    PATH_CLASSES.'Exceptions',
    
    //PATH_CONTROLLER_COMMANDS.'City',

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

?>