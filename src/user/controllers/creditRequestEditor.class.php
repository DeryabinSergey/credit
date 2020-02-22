<?php

class creditRequestEditor extends baseCreditRequestEditor
{  
    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {        
        $mav = parent::postHandleRequest($mav, $request);

        if ($this->isDisplayView($mav)) {
            
        }

        return $mav;
    }    
}