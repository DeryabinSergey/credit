<?php

class userRegister extends CommandContainer
{    
    const ACTION_START      = 'start';
    const ACTION_PHONE      = 'phone';
    const ACTION_RECOVERY   = 'recovery';
    const ACTION_CONFIRM    = 'confirm';
    const ACTION_LOGIN      = 'login';
    const ACTION_LOGOUT     = 'logout';
    
    const SESSION_REGISTRATION  = 'registretion-email';
    
    /**
     * @return userRegister
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_START, UserRegisterStartCommand::create());
        $this->insertCommand(self::ACTION_PHONE, UserRegisterPhoneCommand::create());
        $this->insertCommand(self::ACTION_RECOVERY, UserRegisterRecoveryCommand::create());
        $this->insertCommand(self::ACTION_CONFIRM, UserRegisterConfirmCommand::create());
        $this->insertCommand(self::ACTION_LOGIN, UserRegisterLoginCommand::create());
        $this->insertCommand(self::ACTION_LOGOUT, UserRegisterLogoutCommand::create());

        $this->defaultAction = self::ACTION_START;
        
        $this->secureController = false;

        parent::__construct(User::create());
        
        $this->
            getForm()->
                add(Primitive::string('uuid')->addImportFilter(Filter::textImport())->optional())->
                add(Primitive::integer('pact')->optional())->
                add(Primitive::boolean('needAuth')->optional());
        
        $this->
            map->
                addSource('uuid', RequestType::get())->
                addSource('pact', RequestType::get())->
                addSource('needAuth', RequestType::get());
    }

    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {
        if ($this->isDisplayView($mav) && $mav->getView() == self::COMMAND_SUCCEEDED) {
            if ($this->getForm()->{$this->getActionMethod()}('action') == self::ACTION_START) {
                $mav->
                    setView(RedirectView::create(CommonUtils::makeUrl(get_class($this), array('action' => self::ACTION_PHONE, 'return' => $this->getEncodedCurrentUrl($request)))))->
                    getModel()->drop('id');
            } elseif ($this->getForm()->{$this->getActionMethod()}('action') == self::ACTION_PHONE) {
                $mav->
                    setView(RedirectView::create(CommonUtils::makeUrl(get_class($this), array('action' => self::ACTION_LOGIN, 'pact' => 1, 'return' => $this->getEncodedCurrentUrl($request)))))->
                    getModel()->drop('id');
            } elseif ($this->getForm()->{$this->getActionMethod()}('action') == self::ACTION_CONFIRM) {
                $mav->
                    setView(RedirectView::create(CommonUtils::makeUrl(get_class($this), array('action' => self::ACTION_LOGIN, 'pact' => 2, 'return' => $this->getEncodedCurrentUrl($request)))))->
                    getModel()->drop('id');
            }
        }
        
        $mav = parent::postHandleRequest($mav, $request);

        if ($this->isDisplayView($mav)) {

            Singleton::getInstance('HTMLMetaManager')->
                appendJavaScript('/i/jquery.mask.min.js')->                        
                appendJavaScript('/i/user-register.js')->
                setTitle('Регистрация');
            
            if (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_CONFIRM, self::ACTION_RECOVERY))) {
                Singleton::getInstance('HTMLMetaManager')->setTitle('Восстановление пароля');
            } elseif (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_LOGIN))) {
                Singleton::getInstance('HTMLMetaManager')->setTitle('Авторизация');
            }
            
            if (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_START, self::ACTION_RECOVERY, self::ACTION_LOGIN))) {
                Singleton::getInstance('HTMLMetaManager')->
                    appendJavaScript('https://www.google.com/recaptcha/api.js?render='.GOOGLE_RECAPTCHA_OPEN);
            }
        }

        return $mav;
    }
    
    /**
     * Проверка на наличие объекта по умолчанию, т. к. практически везде одна и та же логика,
     * или контроллер на добавление, импорт - или id Identifialable
     * @return boolean
     */
    protected function checkDefaultActionAndObject() { return true; }
    protected function getDefaultReturnUrl() { return CommonUtils::makeUrl('main'); }
    protected function isDefaultAction() { return false; }
}