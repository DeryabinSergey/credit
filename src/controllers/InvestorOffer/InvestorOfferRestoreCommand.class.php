<?

class InvestorOfferRestoreCommand implements SecurityCommand, EditorCommand
{
    /**
     * @return InvestorOfferRestoreCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = ModelAndView::create();

        if ($form->getValue('ok')) {
            try {
                $subject = $form->getValue('id')->dao()->restore($form->getValue('id'));
                
                if ($form->exists('comment')) {
                    Mail::create()->
                        setTo($form->getValue('id')->getUser()->getEmail())->
                        setFrom(DEFAULT_FROM)->
                        setSubject('Уведомление об восстановлении на портале '.DEFAULT_MAILER)->
                        setText('Предложение инвестирования было восстановлено.'.($form->getValue('comment')?"\r\n\r\nКомментарий администрации: {$form->getDisplayValue('comment')}":''))->
                        send();
                }
			
                return
		    $mav->
                        setView(EditorController::COMMAND_SUCCEEDED)->
                        setModel(Model::create()->set('id', $subject->getId()));
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
            $form->getValue('id') instanceof InvestorOffer &&
            $form->getValue('id')->getUser()->getId() != SecurityManager::getUser()->getId()
        ) {
            $form->add(Primitive::string('comment')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars()));
        }
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof InvestorOffer &&
            $form->getValue('id')->checkPermissions(AclAction::RESTORE_ACTION);
    }
}