<?php

class creditRequestEditor extends CommandContainer
{    
    const ACTION_START      = 'start';
    const ACTION_CONFIRM    = 'confirm';
    
    const SESSION_PHONE  = 'request-phone';
    
    /**
     * @return creditRequestEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_START, CreditRequestStartCommand::create());
        $this->insertCommand(self::ACTION_CONFIRM, CreditRequestConfirmCommand::create());
        $this->insertCommand(self::ACTION_DELETE, CreditRequestDropCommand::create());

        $this->defaultAction = self::ACTION_START;

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
                    setView(RedirectView::create(CommonUtils::makeUrl('controlPanel', array('added' => 1))."#requestList"))->
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
                appendJavaScript('/i/jquery.mask.min.js')->                        
                appendJavaScript('/i/credit-request.js')->
                
                setTitle('Заявка на кредит');
            
            if (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_START))) {
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