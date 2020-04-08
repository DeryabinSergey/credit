<?php

class main extends baseFront
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            Singleton::getInstance('HTMLMetaManager')->setTitle('Кредит под залог автомобиля - FinResurs');
        }
        
        return $model;
    }
}
