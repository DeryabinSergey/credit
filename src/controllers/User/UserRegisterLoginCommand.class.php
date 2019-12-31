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
                try {
                    $user = User::dao()->getByLogic(Expression::andBlock(Expression::eq('email', $form->getValue('email')), Expression::eq('password', hash('sha256', $form->getValue('password')))));
                    if ($user->isBan()) { $form->markCustom('email', self::ERROR_BAN); }
                } catch(ObjectNotFoundException $e) {
                    $form->markCustom('email', self::ERROR_MISMATCH);
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
        $neededPrimitives = array('id', 'email', 'password', 'pact', 'action', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->get('email')->setAllowedPattern(PrimitiveString::MAIL_PATTERN)->addDisplayFilter(Filter::htmlSpecialChars())->addImportFilter(Filter::textImport());
        $form->add(Primitive::string('response')->required());
        
        return $this;
    }

}