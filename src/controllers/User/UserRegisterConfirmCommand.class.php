<?php

class UserRegisterConfirmCommand implements EditorCommand
{
    const ERROR_INTERNAL    = 0x0003;
    const ERROR_DUPLICATE   = 0x0004;
    const ERROR_BAN         = 0x0005;
    
    const ERROR_EXPIRED     = 0x0003;

    /**
     * @return UserRegisterConfirmCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $form->markGood('id');
        $sended = false;
        $userExists = $confirm = $confirmPhone = null;
        
        if ($form->getValue('uuid')) {
            
            try {
                $confirm = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_RECOVERY_EMAIL), Expression::eq('code', $form->getValue('uuid'))));
                $userExists = $confirm->getUser();
            } catch(ObjectNotFoundException $e) { $form->markWrong('uuid'); }            

            if ($process && $confirm instanceof Confirm && $userExists instanceof User) {
                try {
                    $confirmPhone = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_RECOVERY_PHONE), Expression::eq('user_id', $userExists->getId())));
                } catch (ObjectNotFoundException $e) {
                    /** Чувак то оказался долбоебом и не сообразил нажать на кнопочку отправить код - исправляем **/
                    $confirmPhone = 
                        Confirm::dao()->add(
                            Confirm::create()->
                                setType(ConfirmType::create(ConfirmType::TYPE_RECOVERY_PHONE))->
                                setUser($userExists)->
                                setCode(random_int(1, 9999))
                        );
                }
                
            }
            
            if ($process && !$form->getErrors()) {
                try {
                    if ($confirmPhone->getTry() >= 3) throw new ObjectNotFoundException();
                    if ($confirmPhone->getCode() != $form->getValue('code')) {
                        $form->markWrong('code');
                        $confirmPhone->dao()->save($confirmPhone->setTry($confirmPhone->getTry() + 1));
                    }
                } catch (ObjectNotFoundException $e) {
                    $form->markCustom('code', self::ERROR_EXPIRED);
                }
                
                if (!$form->getErrors()) {
                    
                    $password = ViewTextUtils::createRandomPassword(10);
                    
                    $userExists = $userExists->dao()->save($userExists->setPassword(hash('sha256', $password)));
                    
                    if ($userExists->getEmail()) {
                        MimeMailSender::create('Ваш новый пароль на портале '.parse_url(PATH_WEB, PHP_URL_HOST), 'recoveredHtml', 'recoveredText')->
                            setTo($userExists->getEmail(), $userExists->getName())->
                            set('password', $password)->
                            set('user', $userExists)->
                            send();
                    } else {
                        SmsUtils::send("7{$userExists->getPhone()}", "Ваш новый пароль: {$password}");
                        $mav->getModel()->set('pact', 3);
                    }
                    
                    $confirmPhone->dao()->dropById($confirmPhone->getId());
                    $confirm->dao()->dropById($confirm->getId());
                    
                    $mav->setView(EditorController::COMMAND_SUCCEEDED);
                }
            }
        }
        
        if ($mav->getView() != EditorController::COMMAND_SUCCEEDED) {
            $mav->
                getModel()->
                    set('sended', $sended)->
                    set('userExists', $userExists);
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'uuid', 'action', 'go', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->add(Primitive::integer('code')->setMax(9999)->setMin(1)->required());
        
        return $this;
    }

}