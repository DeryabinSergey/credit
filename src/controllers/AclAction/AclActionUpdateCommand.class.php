<?

class AclActionUpdateCommand extends SaveCommand implements SecurityCommand
{
    const ERROR_DUPLICATE = 0x0003;
    
    /**
     * @return AclActionUpdateCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();

        if ($process) {
            try {
                $mav = parent::run($subject, $form, $request);
                if ($mav->getView() == BaseEditor::COMMAND_SUCCEEDED) {
                    Cache::worker(AclAction::dao())->uncacheById($subject->getId());
                }
            } catch(DuplicateObjectException $e) {
                $form->markCustom('aclAction', self::ERROR_DUPLICATE);
            }
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            get('name')->
                addImportFilter(Filter::textImport())->
                addImportFilter(Filter::htmlSpecialChars());
        
        $form->
            get('aclAction')->
                setAllowedPattern(AclAction::ACTION_ALLOWED)->
                addDisplayFilter(Filter::htmlSpecialChars());
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::ACL_ID);
    }
}