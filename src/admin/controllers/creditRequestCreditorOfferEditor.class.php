<?php

class creditRequestCreditorOfferEditor extends baseCreditRequestCreditorOfferEditor
{
    /**
     * @return creditRequestCreditorOfferEditor
     */
    public static function create() { return new self; }

    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {
        $mav = parent::postHandleRequest($mav, $request);

        if ($this->isDisplayView($mav)) {

            Singleton::getInstance('HTMLMetaManager')->                      
                appendJavaScript('/i/jquery.mask.min.js')->                    
                appendJavaScript('/i/credit-request-creditor-offer-editor.js')->
                
                setTitle('Заявка на кредит');
            
        }

        return $mav;
    }
    
    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeUrl('main');
    }
    
}