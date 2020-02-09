<?php

class creditRequestEditor extends baseCreditRequestEditor
{  
    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {        
        $mav = parent::postHandleRequest($mav, $request);

        if ($this->isDisplayView($mav)) {

            if ($this->getForm()->{$this->getActionMethod()}('action') == self::ACTION_CONFIRM) {
            
                Singleton::getInstance('HTMLMetaManager')->                      
                    appendJavaScript('/i/photo-editor.js');
                
            }
        }

        return $mav;
    }    
}