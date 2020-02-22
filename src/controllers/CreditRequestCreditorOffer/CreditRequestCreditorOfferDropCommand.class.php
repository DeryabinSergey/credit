<?

class CreditRequestCreditorOfferDropCommand extends DropCommand implements SecurityCommand, EditorCommand
{
    const ERROR_EXTERNAL = 0x0003;

    /**
     * @return CreditRequestCreditorOfferDropCommand
     */
    public static function create() { return new self; }

    public function checkPermissions(Form $form)
    {
        return $form->getValue('id')->checkPermissions(AclAction::DELETE_ACTION);
    }
}