<?php

class UserRegisterLogoutCommand implements EditorCommand
{
    /**
     * @return UserRegisterLogoutCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        SecurityManager::logout($request);
        $mav->setView(EditorController::COMMAND_SUCCEEDED);
        
        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'action', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        return $this;
    }

}