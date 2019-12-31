<?php

class main extends baseFront
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
        }
        //mail('deryabinsergey@gmail.com', 'test', 'message');
        //Mail::create()->setFrom(DEFAULT_MAILER.' <'.DEFAULT_EMAIL.'>')->setTo('deryabinsergey@gmail.com')->setSubject('Регистрация')->setText('Текст о регистрации')->send();
        //var_dump(SmsUtils::send('79636223355', 'Код подтверждения 1234'));
        return $model;
    }
}