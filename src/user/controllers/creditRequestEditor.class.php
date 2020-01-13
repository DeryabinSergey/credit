<?php

class creditRequestEditor extends CommandContainer
{    
    const ACTION_START      = 'start';
    const ACTION_PHONE      = 'phone';
    const ACTION_RECOVERY   = 'recovery';
    const ACTION_CONFIRM    = 'confirm';
    const ACTION_LOGIN      = 'login';
    const ACTION_LOGOUT     = 'logout';
    
    const SESSION_PHONE  = 'request-phone';
    
    /**
     * @return creditRequestEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_START, CreditRequestStartCommand::create());
        $this->insertCommand(self::ACTION_CONFIRM, CreditRequestConfirmCommand::create());

        $this->defaultAction = self::ACTION_START;
        
        $this->secureController = false;

        parent::__construct(CreditRequest::create());
    }

    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {
        if ($this->isDisplayView($mav) && $mav->getView() == self::COMMAND_SUCCEEDED) {
            if ($this->getForm()->{$this->getActionMethod()}('action') == self::ACTION_START) {
                $mav->
                    setView(RedirectView::create(CommonUtils::makeUrl(get_class($this), array('action' => self::ACTION_CONFIRM, 'return' => $this->getEncodedCurrentUrl($request)))))->
                    getModel()->drop('id');
            }
            if ($this->getForm()->{$this->getActionMethod()}('action') == self::ACTION_CONFIRM) {
                $mav->
                    setView(RedirectView::create(CommonUtils::makeUrl('controlPanel', array('added' => 1))))->
                    getModel()->drop('id');
            }
            
            if ($mav->getView() instanceof RedirectView) {
                $this->dropSecuritySessionVar($request);
            }
        } else {
            if ($this->getForm()->{$this->getActionMethod()}('action') == self::ACTION_START && SecurityManager::isAuth()) {
                $mav->
                    setView(RedirectView::create(CommonUtils::makeUrl(get_class($this), array('action' => self::ACTION_CONFIRM, 'return' => $this->getEncodedCurrentUrl($request)))))->
                    getModel()->drop('id');
            }
            
        }
        
        if ($this->isDisplayView($mav) && $mav->getView() != self::COMMAND_SUCCEEDED) {
            
        }
        
        $mav = parent::postHandleRequest($mav, $request);

        if ($this->isDisplayView($mav)) {

            Singleton::getInstance('HTMLMetaManager')->
                appendStyle('https://cdn.jsdelivr.net/npm/suggestions-jquery@19.8.0/dist/css/suggestions.min.css')->
                appendJavaScript('https://cdn.jsdelivr.net/npm/suggestions-jquery@19.8.0/dist/js/jquery.suggestions.min.js')->
                appendJavaScript('/i/jquery.mask.min.js')->                        
                appendJavaScript('/i/credit-request.js')->
                
                setTitle('Заявка на кредит');
            
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