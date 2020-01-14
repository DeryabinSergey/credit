<?

class CreditRequestDropCommand implements SecurityCommand, EditorCommand
{
    const ERROR_EXTERNAL = 0x0003;

    /**
     * @return CreditRequestDropCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = ModelAndView::create();

        if ($form->getValue('ok')) {
            try {
                $id = $form->getValue('id')->getId();                
                $form->getValue('id')->dao()->markAsDeleted($form->getValue('id'));
                
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
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof CreditRequest &&
            $form->getValue('id')->checkPermissions(AclAction::DELETE_ACTION);
    }
}