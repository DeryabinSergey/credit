<?php

class baseAjaxUserConfirmCode extends baseAjax
{
    const ERROR_MIN_TIME    = 0x0007;
    
    public function  __construct() 
    {
        parent::__construct();
        
        $this->
            form->
                add(Primitive::string('uuid')->addImportFilter(Filter::textImport())->required())->
                
                addMissingLabel('uuid', 'код ссылки не указан')->
                addWrongLabel('uuid', 'код ссылки не распознан')->
                addCustomLabel('errorFlag', self::ERROR_MIN_TIME, 'не прошло минуты для повторной отправки кода');
    }
    
    public function getModel(HttpRequest $request)
    {        
        $model = parent::getModel($request);
        $form = $this->form;
        $confirm = null;
        $codeExists = null;
        
        try {
            $confirm = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_RECOVERY_EMAIL), Expression::eq('code', $form->getValue('uuid'))));
        } catch(ObjectNotFoundException $e) { $form->markWrong('uuid'); }
        
        if ($confirm instanceof Confirm) {
            /**
             * смотрим существование кода для этого номера телефона
             * - если кода нет - создаем и отправляем
             * - если код есть смотрим если прошло больше минуты - обновляем код и отправляем
             * - если код есть и прошло меньше минуты - ошибка
             */
            try {
                $codeExists = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_RECOVERY_PHONE), Expression::eq('user_id', $confirm->getUser()->getId())));
            } catch(ObjectNotFoundException $e) { /* nothin here */ }
            
            if ($codeExists instanceof Confirm) {
                if (Timestamp::compare($codeExists->getCreatedTime(), Timestamp::create("-1 minute")) == -1) {
                    $codeExists->dao()->save($codeExists->setCode(random_int(1, 9999))->setCreatedTime(Timestamp::makeNow())->setTry(0));
                    SmsUtils::send("7{$form->getValue('phone')}", sprintf("Код подтверждения для сброса пароля: %04d", $codeExists->getCode()));
                    $model->set('success', true);
                } else {
                    $form->markCustom('errorFlag', self::ERROR_MIN_TIME);
                }
            } else {
                $codeExists = 
                    Confirm::dao()->add(
                        Confirm::create()->
                            setType(ConfirmType::create(ConfirmType::TYPE_RECOVERY_PHONE))->
                            setUser($confirm->getUser())->
                            setCode(random_int(1, 9999))
                    );
                SmsUtils::send("7{$confirm->getUser()->getPhone()}", sprintf("Код подтверждения для сброса пароля: %04d", $codeExists->getCode()));
                $model->set('success', true);
            }
        }
        
        return $model;
    }
}