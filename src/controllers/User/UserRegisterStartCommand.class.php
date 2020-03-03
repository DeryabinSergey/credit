<?php

class UserRegisterStartCommand implements EditorCommand
{
    const ERROR_INTERNAL    = 0x0003;
    const ERROR_DUPLICATE   = 0x0004;
    const ERROR_BAN         = 0x0005;

    /**
     * @return UserRegisterStartCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $form->markGood('id');
        $subject = $form->getValue('id');
        $userExists = null;
        
        if (Session::exist(userRegister::SESSION_REGISTRATION)) { Session::drop(userRegister::SESSION_REGISTRATION); }
        if (!$form->getValue('accept')) { $form->markWrong('accept'); }
        
        if ($process && !$form->getErrors()) {

            $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, stream_context_create( array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => http_build_query(array('secret' => GOOGLE_RECAPTCHA_CLOSED, 'response' => $form->getValue('response')))) ) )), "true");
            if (!$response['success']) {
                $form->markWrong('response');
            } else {
                try {
                    $userExists = User::dao()->getByLogic(Expression::eq('email', $form->getValue('email')));
                    $form->markCustom('email', self::ERROR_DUPLICATE);
                } catch(ObjectNotFoundException $e) { /* nothin here */ }
            }
            
            if (!$form->getErrors()) {
                
                Session::assign(userRegister::SESSION_REGISTRATION, $form->getValue('email'));
                
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
        $neededPrimitives = array('id', 'email', 'action', 'go', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->get('email')->setAllowedPattern(PrimitiveString::MAIL_PATTERN)->addDisplayFilter(Filter::htmlSpecialChars())->required();
        $form->add(Primitive::boolean('accept')->required());
        $form->add(Primitive::string('response')->required());
        
        return $this;
    }

}