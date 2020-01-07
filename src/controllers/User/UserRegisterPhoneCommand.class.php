<?php

class UserRegisterPhoneCommand implements EditorCommand
{
    const ERROR_INTERNAL    = 0x0003;
    const ERROR_DUPLICATE   = 0x0004;
    const ERROR_BAN         = 0x0005;
    
    const ERROR_EXPIRED     = 0x0003;

    /**
     * @return UserRegisterPhoneCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $form->markGood('id');
        $subject = $form->getValue('id');
        $userEmail = Session::exist(userRegister::SESSION_REGISTRATION) ? Session::get(userRegister::SESSION_REGISTRATION) : false;
        $userExists = null;
        
        if ($userEmail) {

            if ($process && !$form->getErrors()) {
                
                try {
                    $userExists = User::dao()->getByLogic(Expression::eq('phone', $form->getValue('phone')));
                    $form->markCustom('phone', self::ERROR_DUPLICATE);
                } catch(ObjectNotFoundException $e) { /* nothin here */ }
                
                try {
                    $confirm = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_REGISTRATION_PHONE), Expression::eq('phone', $form->getValue('phone'))));
                    if (
                        Timestamp::compare($confirm->getExpiredTime(), Timestamp::makeNow()) == -1 ||
                        $confirm->getTry() >= 3
                    ) throw new ObjectNotFoundException();
                    if ($confirm->getCode() != $form->getValue('code')) {
                        $form->markWrong('code');
                        $confirm->dao()->save($confirm->setTry($confirm->getTry() + 1));
                    }    
                        
                } catch (ObjectNotFoundException $e) {
                    $form->markCustom('code', self::ERROR_EXPIRED);
                }
                
                if (!$form->getErrors()) {
                    
                    $password = ViewTextUtils::createRandomPassword(10);
                    
                    $subject = 
                        User::dao()->
                            add(
                                User::create()->
                                    setName($form->getValue('name'))->
                                    setEmail($userEmail)->
                                    setPassword(hash('sha256', $password))->
                                    setPhone($form->getValue('phone'))
                            );
                    
                    Mail::create()->
                        setTo($userEmail)->
                        setFrom(DEFAULT_FROM)->
                        setSubject('Регистрация на портале '.DEFAULT_MAILER)->
                        setText('Ваш пароль: '.$password)->
                        send();

                    Session::drop(userRegister::SESSION_REGISTRATION);
                    $confirm->dao()->dropById($confirm->getId());
                    $mav->setView(EditorController::COMMAND_SUCCEEDED);
                }
            }

            if ($mav->getView() != EditorController::COMMAND_SUCCEEDED) {
                $mav->
                    getModel()->
                        set('userEmail', $userEmail)->
                        set('userExists', $userExists);
            }
        } else {
            $mav->
                setView(RedirectView::create(CommonUtils::makeUrl('userRegister', array('action' => userRegister::ACTION_START, 'return' => $form->getValue('return')))))->
                getModel()->clean();
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'name', 'action', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->get('name')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars())->required();
        $form->add(Primitive::string('phone')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/", ""))->setAllowedPattern("/\d{10}/is")->required());
        $form->add(Primitive::integer('code')->setMax(9999)->setMin(1)->required());
        
        return $this;
    }

}