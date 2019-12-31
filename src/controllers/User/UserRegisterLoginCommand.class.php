<?php

class UserRegisterLoginCommand implements EditorCommand
{
    const ERROR_INTERNAL    = 0x0003;
    const ERROR_MISMATCH    = 0x0004;
    const ERROR_BAN         = 0x0005;

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
        
        if ($process && !$form->getErrors()) {

            $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, stream_context_create( array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => http_build_query(array('secret' => GOOGLE_RECAPTCHA_CLOSED, 'response' => $form->getValue('response')))) ) )), "true");
            if (!$response['success']) {
                $form->markWrong('response');
            } else {
                
                if (preg_match(PrimitiveString::MAIL_PATTERN, $form->getValue('credentials'))) {
                    try {
                        $user = User::dao()->getByLogic(Expression::andBlock(Expression::eq('email', $form->getValue('credentials')), Expression::eq('password', hash('sha256', $form->getValue('password')))));
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
                            $user = User::dao()->getByLogic(Expression::andBlock(Expression::eq('phone', $phone)), Expression::eq('password', hash('sha256', $form->getValue('password'))));
                        } catch(ObjectNotFoundException $e) { 
                            $form->markCustom('credentials', self::ERROR_MISMATCH);
                        }
                    }
                }

                if ($user instanceof User && $user->isBan()) {
                    $form->markCustom('credentials', self::ERROR_BAN);
                }
            }
            
            if (!$form->getErrors()) {
                if (!$user->isActive()) {
                    $user = $user->dao()->save($user->setActive(true));
                }
                SecurityManager::setUser($user);
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
        $neededPrimitives = array('id', 'password', 'pact', 'action', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->
            add(Primitive::string('credentials')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars())->required())->
            add(Primitive::string('response')->required());
        
        return $this;
    }
}