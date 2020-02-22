<?php

class creditRequestEditor extends CommandContainer implements UserController
{        
    /**
     * @return creditRequestEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_VIEW, CreditRequestCreditorViewCommand::create());

        $this->defaultAction = self::ACTION_VIEW;

        parent::__construct(CreditRequestCreditor::create());
    }

    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {        
        $mav = parent::postHandleRequest($mav, $request);

        if ($this->isDisplayView($mav)) {

            Singleton::getInstance('HTMLMetaManager')->                        
                appendJavaScript('/i/jquery.mask.min.js')->              
                appendJavaScript('/i/credit-request.js')->
                
                setTitle('Заявка на кредит');
            

            if (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_VIEW))) {
            
                
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
    protected function getDefaultReturnUrl() { return CommonUtils::makeUrl('creditRequestList'); }
    protected function isDefaultAction() { return false; }
}