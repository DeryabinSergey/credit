<?php

class CreditRequestStartCommand implements EditorCommand
{
    const ERROR_INTERNAL        = 0x0003;
    const ERROR_DUPLICATE       = 0x0004;
    const ERROR_BAN             = 0x0005;
    const ERROR_AUTH_ENABLED    = 0x0006;
    
    const ERROR_EXPIRED         = 0x0003;

    /**
     * @return CreditRequestStartCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $form->markGood('id');
        $subject = $form->getValue('id');
        $userExists = $confirm = null;
        
        if (!SecurityManager::isAuth() && !SecurityManager::isAuthEnabled()) { $form->markCustom('phone', self::ERROR_AUTH_ENABLED); }
        
        /** Отправляем код подтверждения, если чувак не додумался нажать на кнопку отправить код **/
        if ($process && !$form->getError('phone') && $form->getValue('phone')) {    
            try {
                $confirm = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_CREDIT_REQUEST), Expression::eq('phone', $form->getValue('phone'))));
            } catch (ObjectNotFoundException $e) {
                $confirm = 
                    Confirm::dao()->add(
                        Confirm::create()->
                            setType(ConfirmType::create(ConfirmType::TYPE_CREDIT_REQUEST))->
                            setPhone($form->getValue('phone'))->
                            setCode(random_int(1, 9999))
                    );
                SmsUtils::send("7{$form->getValue('phone')}", sprintf("Код подтверждения для оформления заявки на кредит: %04d", $confirm->getCode()));
            }            
        }
        
        if ($process && !$form->getErrors()) {

            if (!$form->getValue('accept')) { $form->markWrong('accept'); }
            $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, stream_context_create( array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => http_build_query(array('secret' => GOOGLE_RECAPTCHA_CLOSED, 'response' => $form->getValue('response')))) ) )), "true");
            if (!$response['success']) {
                $form->markWrong('response');
            } else {
                try {
                    $userExists = User::dao()->getByLogic(Expression::eq('phone', $form->getValue('phone')));
                } catch(ObjectNotFoundException $e) { /* nothin here */ }
                
                try {
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
            }
            
            if (!$form->getErrors()) {
                
                if (!$userExists instanceof User) {
                    $password = ViewTextUtils::createRandomPassword(10);

                    $userExists = 
                        User::dao()->
                            add(
                                User::create()->
                                    setName($form->getValue('phone'))->
                                    setPassword(hash('sha256', $password))->
                                    setPhone($form->getValue('phone'))->
                                    setActive(true)
                            );

                    SmsUtils::send("7{$form->getValue('phone')}", "Ваш пароль для входа на сайт: {$password}");
                    SecurityManager::setUser($userExists, true, $request);
                } elseif ($userExists instanceof User && !SecurityManager::isAuth()) {
                    SecurityManager::setUser($userExists, true, $request);
                }            
                $confirm->dao()->dropById($confirm->getId());
                
                $mav->setView(EditorController::COMMAND_SUCCEEDED);
            }
        }

        if ($mav->getView() != EditorController::COMMAND_SUCCEEDED) {
            $mav->
                getModel()->
                    set('userExists', $userExists);
        }
        
        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'securityCode', 'action', 'go', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->add(Primitive::boolean('accept')->required());
        $form->add(Primitive::string('phone')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/", ""))->setAllowedPattern("/\d{10}/is")->required());
        $form->add(Primitive::integer('code')->setMax(9999)->setMin(1)->required());
        $form->add(Primitive::string('response')->required());
        
        return $this;
    }

}