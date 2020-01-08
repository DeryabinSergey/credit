<?

class CreditorDropCommand implements SecurityCommand, EditorCommand
{
    const ERROR_EXTERNAL = 0x0003;

    /**
     * @return CreditorDropCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = ModelAndView::create();

        if ($form->getValue('ok')) {
            try {
                $mail = null;
                $id = $form->getValue('id')->getId();
                $daoMethod = $form->getValue('id')->isActive() ? 'markAsDeleted' : 'drop';
                if ($form->exists('comment')) {
                    $mail = Mail::create()->
                        setTo($form->getValue('id')->getUser()->getEmail())->
                        setFrom(DEFAULT_FROM)->
                        setSubject('Уведомление об удалении')->
                        setText('Ваша кредитная организация была удалена.'.($form->getValue('comment')?"\r\n\r\nКомментарий администрации: {$form->getDisplayValue('comment')}":''));
                }
                
                $form->getValue('id')->dao()->{$daoMethod}($form->getValue('id'));
                if ($mail) { $mail->send(); }
                
                return
		    $mav->
                        setView(EditorController::COMMAND_SUCCEEDED)->
                        setModel(Model::create()->set('id', $id));                
            } catch(DatabaseException $e) {
                $form->markCustom('id', self::ERROR_EXTERNAL);
            }
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->add(Primitive::boolean('ok'));
        
        if (
            SecurityManager::isAllowedAction(AclAction::DELETE_ACTION, AclContext::CREDITOR_ID) &&
            $form->getValue('id') instanceof Creditor &&
            $form->getValue('id')->getUser()->getId() != SecurityManager::getUser()->getId()
        ) {
            $form->add(Primitive::string('comment')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars()));
        }
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof Creditor &&
            $form->getValue('id')->checkPermissions(AclAction::DELETE_ACTION);
    }
}