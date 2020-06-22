<?php

class SecurityManager
{
    /**
     * имя ключа сессии идентификатора авторизованного пользователя
     */
    const SESSION_VAR = 'user_id';
    /**
     * имя ключа сессии сохранения последнего визита пользователя
     */
    const LAST_VISIT_VAR = 'last_visit';
    /**
     * Имя ключа куки авторизованного пользователя
     */
    const COOKIE_USER_NAME = 'authUser';
    /**
     * Имя ключа куки кода сайта
     */
    const COOKIE_CODE_NAME = 'site-code';

    /**
     * Имя ключа cookie уникального ключа браузера
     * @var String
     */
    protected static $codeCookieName = null;
    /**
     * Имя ключа cookie авторизованного на сайте пользователя
     * @var String
     */
    protected static $cookieName = null;

    /**
     * Авторизованный на сайте пользователь
     * @var User
     */
    public static $user = null;
    /**
     * Уникальный код браузера пользователя из cookie
     * @var String
     */
    protected static $code = null;

    protected static $lastVisit = null;
    protected static $initLastVisit = false;

    private function  __construct() { Assert::isUnreachable('I am can not be created, only static use'); }

    public static function setInitLastVisit($value = true)
    {
        self::$initLastVisit = $value === true;
    }

    /**
     * Установка имени куки авторизации
     * @param String $name 
     */
    public static function setCookieName($name)
    {
        SecurityManager::$cookieName = $name;
    }

    public static function setCodeCookieName($name)
    {
        SecurityManager::$codeCookieName = $name;
    }

    /**
     * Вернет текущего авторизованного пользователя или null
     *
     * @return User | null
     */
    public static function getUser()
    {
        return self::$user;
    }

    protected static function initLastVisit()
    {
        if (Session::isStarted() && Session::exist(self::LAST_VISIT_VAR)) {
            self::$lastVisit = Timestamp::create(Session::get(self::LAST_VISIT_VAR));
        }
    }

    protected static function setLastVisit(Timestamp $lastVisit)
    {
        if (Session::isStarted()) {
            Session::assign(self::LAST_VISIT_VAR, $lastVisit->toString());
            self::$lastVisit = $lastVisit;
        }
    }

    public static function getLastVisit()
    {
        return self::$lastVisit;
    }

    /**
     *  Установит пользователя в сессию
     *
     * @param User $user
     * @param Boolean $remember
     */
    public static function setUser(User $user, $remember = false, HttpRequest $request = null, $addLog = true, $cookieOnly = false)
    {
        Assert::isNotNull(self::$cookieName);
        if (!$cookieOnly) { Assert::isNotNull(self::$code); }

        self::$user = $user;
        
        if (!$cookieOnly && Session::isStarted()) {
            Session::assign(self::SESSION_VAR, $user->getId());
        }        
        
        if ($cookieOnly) {
            Cookie::create(self::$cookieName)->
                setValue(base64_encode("{$user->getId()}:".hash('sha256', self::getCode().$user->getPassword())))->
                setPath('/')->
                setHttpOnly()->
                setSecure()->
                setSameSiteLax()->
                httpSet();
        } elseif ($remember) {
            Cookie::create(self::$cookieName)->
                setValue(base64_encode("{$user->getId()}:".hash('sha256', self::getCode().$user->getPassword())))->
                setDomain(COOKIE_DOMAIN)->
                setPath('/')->
                setMaxAge(Constants::COOKIE_LIFETIME)->
                setHttpOnly()->
                setSecure()->
                setSameSiteLax()->
                httpSet();
        }

        if (
            self::getUser() instanceof User &&
            self::getUser()->getLastLog() instanceof Timestamp &&
            self::$initLastVisit
        ) {
            self::setLastVisit(self::getUser()->getLastLog());
        }

        if ($addLog) self::authLog($request, true);
    }

    public static function authUser(HttpRequest $request, $cookieOnly = false)
    {
        Assert::isNotNull(self::$cookieName);
        $setVisit = false; // При успешной авторизации не устанавливать дату последнего визита

        if (self::$initLastVisit) { self::initLastVisit(); }

        if (!$cookieOnly && $request->hasSessionVar(self::SESSION_VAR)) {
            try {
                $user = User::dao()->getById($request->getSessionVar(self::SESSION_VAR));
                if ($user->isBan()) { throw new WrongStateException('user banned or inactive'); }
                // При усиленной безопасности, смотрим при получении пользователя из сесси, что не изменился IP и код
                // Для начала пусть авторизация сохраняется при наличии одного верного предыдущего параметра
                if ($user->getSecurityType()->isStrong() && !self::checkSecurity($user, $request)) {
                    throw new WrongStateException('unknown user by security credentials');
                }
                self::$user = $user;
            } catch (Exception $e) {
                self::logout($request);
                return false;
            }
        }

        if (!self::$user instanceof User && $request->hasCookieVar(self::$cookieName)) {
            try {
                list($userId, $password) = explode(":", base64_decode($request->getCookieVar(self::$cookieName)));
                $user = User::dao()->getById($userId);
                if (hash('sha256', self::getCode().$user->getPassword()) != $password) {
                    $success = false;
                    // Пытаемся достать из логов
                    try {
                        $logs = SecurityLog::dao()->getLastUserLogs($user, Constants::USER_AUTH_LOG_DEEP * 2);
                        foreach($logs as $log) {
                            if ($log->getSid() != self::getCode()) {
                                $success = hash('sha256', $log->getSid().$user->getPassword()) == $password;
                                if ($success) {
                                    $cookie = 
                                        Cookie::create(self::$cookieName)->
                                            setValue(base64_encode("{$user->getId()}:".hash('sha256', self::getCode().$user->getPassword())))->
                                            setPath('/')->
                                            setHttpOnly()->
                                            setSecure()->
                                            setSameSiteLax();
                                    if (!$cookieOnly) {
                                        $cookie->setDomain(COOKIE_DOMAIN)->setMaxAge(Constants::COOKIE_LIFETIME);
                                    }            
                                    $cookie->httpSet();
                                    break;
                                }
                            }
                        }
                    } catch(ObjectNotFoundException $e) { }
                    
                    if (!$success) throw new WrongStateException('wrong cookie password');
                }
                if ($user->isBan()) { throw new WrongStateException('user banned or inactive'); }
                // При авторизации по куки для усиленной авторизации проверяем соответствие хотя бы одному параметру,
                // При повышенной - обоим параметрам
                if (
                    !$cookieOnly &&
                    !$user->getSecurityType()->isBasic() &&
                    !self::checkSecurity($user, $request, $user->getSecurityType()->isStrong())
                ) { throw new WrongStateException('unknown user by security credentials'); }
                self::$user = $user;
                // При успешной авторизации по кукам ставим дату последнего захода и дальше будем ходить по сессии
                if (!$cookieOnly) {
                    Session::assign(self::SESSION_VAR, $user->getId());
                    $setVisit = true;
                }
            } catch (Exception $e) {
                if (!$cookieOnly) { self::logout($request); }
                return false;
            }
        }

        if (
            self::getUser() instanceof User &&
            self::getUser()->getLastLog() instanceof Timestamp &&
            self::$initLastVisit &&
            !self::$lastVisit instanceof Timestamp
        ) {
            self::setLastVisit(self::getUser()->getLastLog());
        }

        if (self::$user instanceof User && !$cookieOnly) {
            self::authLog($request, $setVisit);
        }
    }

    /**
     * Проверка безопасности, что пользователь уже был на сайте, с такими данными (кодом и IP адресом)
     * Используется глубокая проверка - выборка n последних записей для сайта
     *
     * @param User $user
     * @param HttpRequest $request
     * @param Boolean $strong - должны совпасть оба параметра IP и код
     * @return Boolean
     */
    protected static function checkSecurity(User $user, HttpRequest $request, $strong = false)
    {
        if (
            ($strong && $user->getRealIp() == $request->getServerVar('REMOTE_ADDR') && $user->getSid() == self::$code) ||
            (!$strong && ($user->getRealIp() == $request->getServerVar('REMOTE_ADDR') || $user->getSid() == self::$code))
        ) {
            return true;
        }

        // Пытаемся достать из логов
        try {
            $logs = SecurityLog::dao()->getLastUserLogs($user, Constants::USER_AUTH_LOG_DEEP);
            foreach($logs as $log) {
                if (
                    ($strong && $request->getServerVar('REMOTE_ADDR') == $log->getRealIp() && self::$code == $log->getSid()) ||
                    (!$strong && ($request->getServerVar('REMOTE_ADDR') == $log->getRealIp() || self::$code == $log->getSid()))
                ) {
                    return true;
                }
            }
        } catch(ObjectNotFoundException $e) { }

        return false;
    }
    
    /**
     * Снятие авторизации пользователя
     * @param HttpRequest $request
     */
    public static function logout(HttpRequest $request)
    {
        if (self::$initLastVisit) {
            self::$lastVisit = null;
            if (Session::isStarted()) { Session::drop(self::LAST_VISIT_VAR); }
        }
        
        self::$user = null;
        if (Session::isStarted()) { Session::drop(self::SESSION_VAR); }
        Cookie::create(self::$cookieName)->setValue('')->setDomain(COOKIE_DOMAIN)->setPath('/')->setMaxAge(-1000)->setHttpOnly()->setSecure()->setSameSiteLax()->httpSet();
        $cookie = array();
        foreach($request->getCookie() as $cookieName => $cookieVar) {
            if ($cookieName != self::COOKIE_USER_NAME)
                $cookie[$cookieName] = $cookieVar;
        }
        $request->setCookie($cookie);
    }

    /**
     * Логирование активности пользователя. Если данные по коду или IP не совпадают - обновлять данные. 
     * В зависимости от настроек Constants::USER_LOG_ACTION обновлять активность при каждой загрузке страницы
     * @param HttpRequest $request
     * @param type $setVisit установить время последней авторизации, значит была авторизация или через форму или через куки
     */
    private static function authLog(HttpRequest $request, $setVisit = false)
    {
        $addLog = false;
        $userUpdate = false;

        if (Constants::USER_LOG_ACTION) {
            self::$user->setLastLog(Timestamp::makeNow());
            $userUpdate = true;
        }
        
        if ($setVisit) {
            self::$user->setLastVisit(Timestamp::makeNow());
            $userUpdate = true;
        }

        if (self::$user->getSid() != self::$code) {
            self::$user->setSid(self::$code);
            $userUpdate = $addLog = true;
        }

        if (self::$user->getRealIp() != $request->getServerVar('REMOTE_ADDR')) {
            self::$user->setRealIp($request->getServerVar('REMOTE_ADDR'));
            $userUpdate = $addLog = true;
        }

        if ($userUpdate) {
            self::$user = self::$user->dao()->save(self::$user);
        }
        
        if ($addLog) {
            SecurityLog::dao()->add(
                SecurityLog::create()->
                    setUser(self::$user)->
                    setSid(self::$user->getSid())->
                    setIp(self::$user->getIp())
            );
        }
    }

    /**
     * Проверка на авторизацию
     * @return Boolean
     */
    public static function isAuth() { return self::$user instanceof User; }

    /**
     * Вернет признак возможности авторизации - если код установлен, значит включены куки - значит можно
     * @return Boolean
     */
    public static function isAuthEnabled() { return !empty(self::$code); }

    /**
     * Инициализация кода компьютера, если его нет - генерируем и пытаемся установить
     * @param HttpRequest $request
     */
    public static function initCode(HttpRequest $request)
    {
        Assert::isNotNull(self::$codeCookieName);

        if ($request->hasCookieVar(self::$codeCookieName)) {
            self::$code = $request->getCookieVar(self::$codeCookieName);
        } else {
            self::logout($request); // если кода браузера нет - значит авторизации тоже быть не может
            $code = CommonUtils::genUuid();
            Cookie::create(self::$codeCookieName)->setHttpOnly(true)->setSecure(true)->setValue($code)->setDomain(COOKIE_DOMAIN)->setPath('/')->setMaxAge(Constants::COOKIE_LIFETIME)->setSameSiteLax()->httpSet();
        }
    }

    /**
     * Получение уникального кода браузера
     * @return string
     */
    public static function getCode() { return self::$code; }

    /**
     * Проверка возможности действия по контексту.
     * По подчиненности:
     * 1. Право без секции - распространяется на все секции
     * 2. Право с секцией - распространяется только на текущую секцию
     * 
     * @param string $actionLabel
     * @param int $contextId
     * @param int $sectionId
     * @return bool
     */
    public static function isAllowedAction($actionLabel, $contextId, $sectionId = null)
    {
        static $aclRightList = array();
        $access = false;

        if (self::isAuth()) {
            if (self::getUser()->getGroup() instanceof AclGroup) {
                if (!count($aclRightList)) {
                    $aclRightList = self::getUser()->getGroup()->getRights()->getList();
                }
                
                /**
                 * Если есть глобальные права - значит они подойдут и для какой то определенной секции
                 */
                foreach ($aclRightList as $aclRight) {
                    if (
                        $aclRight->getRight()->getAction()->getAction() == $actionLabel &&
                        $aclRight->getRight()->getContextId() == $contextId &&
                        (
                            !$aclRight->getRight()->getSectionId() ||
                            ($sectionId && $aclRight->getRight()->getSectionId() == $sectionId)                            
                        )
                    ) {
                        $access = true;
                        break;
                    }
                }

                /**
                 * Если глопальных прав нет и выбрана конкретная секция - пытаемся посмотреть по ней
                 */
                if (!$access && !is_null($sectionId)) {
                    $access = AclGroup::dao()->isAllowedAction(self::getUser()->getGroup()->getId(), $actionLabel, $contextId, $sectionId);
                }
            }
        }

        return $access;
    }
    
    public static function getAllowedContextSectionList($actionLabel, $contextId)
    {
        $list = array();
        
        if (self::isAuth() && self::getUser()->getGroup() instanceof AclGroup) {
            $list = AclGroup::dao()->getAllowedContextSectionList(self::getUser()->getGroup()->getId(), $actionLabel, $contextId);
        }
        
        return $list;
    }

    /**
     * Общая проверка на возможность авторизации под определенным пользователем с определенного IP
     * @param User $user Пользователь под которым авторизуются
     * @param type $ip IP адрес компьютера с которого авторизуются
     * @return Boolean
     */
    public static function canAuth(User $user, $ip)
    {
        return self::canAuthByUser($user) && self::canAuthByIp($ip);
    }

    /**
     * Проверка на возможность авторизации на портале под конкретным пользователем
     * Если было слишком много неудачных попыток авторизации для конкретного пользователя - перестать проверять данные
     * @param User $user Пользователь, под которым пытаются авторизоваться
     * @return boolean
     */
    public static function canAuthByUser(User $user)
    {
        $criteria = Criteria::create(AuthLog::dao())->
            addProjection(Projection::property('id'))->
            add(Expression::eq('user', $user->getId()))->
            add(Expression::gtEq('createdTime', Timestamp::create(sprintf("%+d hour", -Constants::AUTH_USER_PERIOD))->toString()))->
            addOrder(OrderBy::create('createdTime')->desc());

        try {
            $ids = $criteria->getDao()->getCustomList($criteria->toSelectQuery(), Cache::DO_NOT_CACHE);
            if (count($ids) <= Constants::AUTH_USER_TRY) {
                return true;
            } else {
                $lastLog = AuthLog::dao()->getById($ids[0]['id']);
                return Timestamp::compare(Timestamp::makeNow(), $lastLog->getCreatedTime()->modify(sprintf("+%d minute", Constants::AUTH_DELAY))) == 1;
            }
        } catch(ObjectNotFoundException $e) {
            return true;
        }
    }

    /**
     * Проверка на возможность авторизации на портале пользователя с переданным IP. 
     * Если было слишком много неудачных попыток авторизации от конкретного IP - перестать проверять данные
     * @param type $ip IP адрес пользователя для проверки возможности авторизации
     * @return boolean
     */
    public static function canAuthByIp($ip)
    {
        $criteria = Criteria::create(AuthLog::dao())->
            addProjection(Projection::property('id'))->
            add(Expression::eq('ip', ip2long($ip)))->
            add(Expression::gtEq('createdTime', Timestamp::create(sprintf("%+d hour", -Constants::AUTH_IP_PERIOD))->toString()))->
            addOrder(OrderBy::create('createdTime')->desc());

        try {
            $ids = $criteria->getDao()->getCustomList($criteria->toSelectQuery(), Cache::DO_NOT_CACHE);
            if (count($ids) <= Constants::AUTH_IP_TRY) {
                return true;
            } else {
                $lastLog = AuthLog::dao()->getById($ids[0]['id']);
                return Timestamp::compare(Timestamp::makeNow(), $lastLog->getCreatedTime()->modify(sprintf("+%d minute", Constants::AUTH_DELAY))) == 1;
            }
        } catch(ObjectNotFoundException $e) {
            return true;
        }
    }
}