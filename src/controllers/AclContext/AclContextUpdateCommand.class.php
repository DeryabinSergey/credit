<?

class AclContextUpdateCommand extends SaveCommand implements SecurityCommand
{    
    const ERROR_DUPLICATE = 0x0003;
    
    /**
     * @return AclContextUpdateCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
            
        if ($process) {
            try {
                $mav = parent::run($subject, $form, $request);
            } catch(DuplicateObjectException $e) {
                $form->markCustom('name', self::ERROR_DUPLICATE);
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

        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::ACL_ID);
    }
}