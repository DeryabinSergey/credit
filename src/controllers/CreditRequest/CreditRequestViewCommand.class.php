<?

class CreditRequestViewCommand implements SecurityCommand, EditorCommand
{
    /**
     * @return CreditRequestViewCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        return ModelAndView::create();
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof CreditRequest &&
            $form->getValue('id')->checkPermissions(AclAction::VIEW_ACTION);
    }
}