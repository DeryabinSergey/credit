<?php

class UserRegisterLoginCommand implements EditorCommand
{
    const ERROR_INTERNAL        = 0x0003;
    const ERROR_MISMATCH        = 0x0004;
    const ERROR_BAN             = 0x0005;
    const ERROR_AUTH_ENABLED    = 0x0006;
    const ERROR_HAMMER          = 0x0007;

    /**
     * @return UserRegisterLoginCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $form->markGood('id');
        $user = null;
        
        if (!SecurityManager::isAuthEnabled()) $form->markCustom ('credentials', self::ERROR_AUTH_ENABLED);
        if (!SecurityManager::canAuthByIp($request->getServerVar('REMOTE_ADDR')))  $form->markCustom ('credentials', self::ERROR_HAMMER);
        
        if ($process && !$form->getErrors()) {

            $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, stream_context_create( array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => http_build_query(array('secret' => GOOGLE_RECAPTCHA_CLOSED, 'response' => $form->getValue('response')))) ) )), "true");
            if (!$response['success']) {
                $form->markWrong('response');
            } else {
                
                if (preg_match(PrimitiveString::MAIL_PATTERN, $form->getValue('credentials'))) {
                    try {
                        $user = User::dao()->getByLogic(Expression::eq('email', $form->getValue('credentials')));
                    } catch(ObjectNotFoundException $e) { 
                        $form->markCustom('credentials', self::ERROR_MISMATCH);
                    }
                } else {
                    $phone = preg_replace("/[^\d]/isu", "", $form->getValue('credentials'));
                    $phoneLen = mb_strlen($phone);
                    $phone = mb_substr($phone, $phoneLen > 10 ? $phoneLen - 10 : 0, 10);

                    if (mb_strlen($phone) < 10) {
                        $form->markWrong('credentials');
                    } else {
                        try {
                            $user = User::dao()->getByLogic(Expression::eq('phone', $phone));
                        } catch(ObjectNotFoundException $e) { 
                            $form->markCustom('credentials', self::ERROR_MISMATCH);
                        }
                    }
                }
                
                if ($user instanceof User) {
                    if (SecurityManager::canAuthByUser($user)) {
                        if ($user->getPassword() == hash('sha256', $form->getValue('password'))) {
                            if ($user->isBan()) {
                                $form->markCustom('credentials', self::ERROR_BAN);
                            }
                        } else {
                            $form->markCustom('credentials', self::ERROR_MISMATCH);
                            AuthLog::dao()->add(
                                AuthLog::create()->
                                    setSid(SecurityManager::getCode())->
                                    setRealIp($request->getServerVar('REMOTE_ADDR'))->
                                    setUser($user)
                            );
                        }
                    } else {
                        $form->markCustom ('credentials', self::ERROR_HAMMER);
                    }
                } else {
                    AuthLog::dao()->add(
                        AuthLog::create()->
                            setSid(SecurityManager::getCode())->
                            setRealIp($request->getServerVar('REMOTE_ADDR'))->
                            setLogin($form->getValue('credentials'))
                    );
                }
            }
            
            if (!$form->getErrors()) {
                if (!$user->isActive()) {
                    $user = $user->dao()->save($user->setActive(true));
                }
                SecurityManager::setUser($user, $form->getValue('remember'), $request);
                $mav->setView(EditorController::COMMAND_SUCCEEDED);
            }
        }

        if ($mav->getView() != EditorController::COMMAND_SUCCEEDED) {
            $mav->
                getModel()->
                    set('user', $user);
        }
        
        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'securityCode', 'password', 'pact', 'needAuth', 'action', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->
            add(Primitive::string('credentials')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars())->required())->
            add(Primitive::string('response')->required())->
            add(Primitive::boolean('remember'));
        
        return $this;
    }
}