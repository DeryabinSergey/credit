<?

class CreditRequestCreditorOfferRejectCommand extends MakeCommand implements SecurityCommand, EditorCommand
{
    const ERROR_EXTERNAL = 0x0003;

    /**
     * @return CreditRequestCreditorOfferRejectCommand
     */
    public static function create() { return new self; }
    
    protected function daoMethod() { return 'reject'; }

    public function checkPermissions(Form $form)
    {
        return $form->getValue('id')->checkPermissions(AclAction::REJECT_ACTION);
    }
}