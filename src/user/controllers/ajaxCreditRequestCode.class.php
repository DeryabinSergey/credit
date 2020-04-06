<?php

class ajaxCreditRequestCode extends baseAjax
{
    const ERROR_MIN_TIME    = 0x0007;
    
    public function  __construct() 
    {
        parent::__construct();
        
        $this->
            form->
                add(Primitive::string('phone')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/", ""))->setAllowedPattern("/\d{10}/is")->required())->
                
                addMissingLabel('phone', 'номер телефона не указан')->
                addWrongLabel('phone', 'номер телефона указан неверно')->
                addCustomLabel('errorFlag', self::ERROR_MIN_TIME, 'не прошло минуты для повторной отправки кода');
    }
    
    public function getModel(HttpRequest $request)
    {        
        $model = parent::getModel($request);
        $form = $this->form;
        $codeExists = null;
        
        /**
         * смотрим существование кода для этого номера телефона
         * - если кода нет - создаем и отправляем
         * - если код есть смотрим если прошло больше минуты - обновляем код и отправляем
         * - если код есть и прошло меньше минуты - ошибка
         */
        try {
            $codeExists = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_CREDIT_REQUEST), Expression::eq('phone', $form->getValue('phone'))));
        } catch(ObjectNotFoundException $e) { /* nothin here */ }

        if ($codeExists instanceof Confirm) {
            if (Timestamp::compare($codeExists->getCreatedTime(), Timestamp::create("-1 minute")) == -1) {
                $codeExists->dao()->save($codeExists->setCode(random_int(1, 9999))->setCreatedTime(Timestamp::makeNow())->setTry(0));
                SmsUtils::send("7{$form->getValue('phone')}", sprintf("Код подтверждения для оформления заявки на кредит: %04d", $codeExists->getCode()));
                //$model->set('success', true);
            } else {
                //$form->markCustom('errorFlag', self::ERROR_MIN_TIME);
            }
            /** Если время не прошло - сообщение что код все равно отправлен, иначе тупят **/
            $model->set('success', true);
        } else {
            $codeExists = 
                Confirm::dao()->add(
                    Confirm::create()->
                        setType(ConfirmType::create(ConfirmType::TYPE_CREDIT_REQUEST))->
                        setPhone($form->getValue('phone'))->
                        setCode(random_int(1, 9999))
                );
            SmsUtils::send("7{$form->getValue('phone')}", sprintf("Код подтверждения для оформления заявки на кредит: %04d", $codeExists->getCode()));
            $model->set('success', true);
        }
        
        return $model;
    }
}