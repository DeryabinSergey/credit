<?php

class UserRegisterRecoveryCommand implements EditorCommand
{
    const ERROR_INTERNAL    = 0x0003;

    /**
     * @return UserRegisterRecoveryCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $form->markGood('id');
        $subject = $form->getValue('id');
        $userExists = null;
        $userFindByEmail = false;
        
        if ($process && !$form->getErrors()) {
            
            $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, stream_context_create( array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => http_build_query(array('secret' => GOOGLE_RECAPTCHA_CLOSED, 'response' => $form->getValue('response')))) ) )), "true");
            if (!$response['success']) {
                $form->markWrong('response');
            } else {
                if ($form->getValue('credentials')) {
                    if (preg_match(PrimitiveString::MAIL_PATTERN, $form->getValue('credentials'))) {
                        try {
                            $userExists = User::dao()->getByLogic(Expression::eq('email', $form->getValue('credentials')));
                            $userFindByEmail = true;
                        } catch(ObjectNotFoundException $e) { 
                            $form->markWrong('credentials');
                        }
                    } else {
                        $phone = preg_replace("/[^\d]/isu", "", $form->getValue('credentials'));
                        $phoneLen = mb_strlen($phone);
                        $phone = mb_substr($phone, $phoneLen > 10 ? $phoneLen - 10 : 0, 10);
                        
                        if (mb_strlen($phone) < 10) {
                            $form->markWrong('credentials');
                        } else {
                            try {
                                $userExists = User::dao()->getByLogic(Expression::eq('phone', $phone));
                            } catch(ObjectNotFoundException $e) { 
                                $form->markWrong('credentials');
                            }
                        }
                    }
                } else {
                    $form->markMissing('credentials');
                }
            }
        }
        
        if ($userExists instanceof User && !$userExists->isBan()) {
            try {
                $confirm = 
                    Confirm::dao()->
                        getByLogic(
                            Expression::andBlock(
                                Expression::eq('user_id', $userExists->getId()),
                                Expression::eq('type_id', ConfirmType::TYPE_RECOVERY_EMAIL)
                            )
                        );
            } catch(ObjectNotFoundException $e) {
                $confirm = 
                    Confirm::dao()->
                        add(
                            Confirm::create()->
                                setUser($userExists)->
                                setType(ConfirmType::create(ConfirmType::TYPE_RECOVERY_EMAIL))->
                                setCode(CommonUtils::genUuid())
                        );
            }
            
            if ($userExists->getEmail()) {
                Mail::create()->
                    setTo($userExists->getEmail())->
                    setFrom(DEFAULT_FROM)->
                    setSubject('Сброс пароля на портале '.DEFAULT_MAILER)->
                    setText('Ссылка для сброса пароля: '.CommonUtils::makeUrl('userRegister', array('action' => userRegister::ACTION_CONFIRM, 'uuid' => $confirm->getCode())))->
                    send();
            } else {
                $mav->setView(RedirectView::create(CommonUtils::makeUrl('userRegister', array('action' => userRegister::ACTION_CONFIRM, 'uuid' => $confirm->getCode()))));
            }
            
        }

        if ($mav->getView() != EditorController::COMMAND_SUCCEEDED) {
            $mav->
                getModel()->
                    set('userFindByEmail', $userFindByEmail)->
                    set('userExists', $userExists);
        }
        
        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'securityCode', 'go', 'action', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->
            add(Primitive::string('credentials')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars()))->
            add(Primitive::string('response')->required());
        
        return $this;
    }

}