<?

class AclActionAddCommand extends AddCommand implements SecurityCommand
{
    const ERROR_DUPLICATE = 0x0003;

    /**
     * @return AclActionAddCommand
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
                addDisplayFilter(Filter::htmlSpecialChars());
        
        $form->
            get('aclAction')->
                setAllowedPattern(AclAction::ACTION_ALLOWED)->
                addDisplayFilter(Filter::htmlSpecialChars());
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::ADD_ACTION, AclContext::ACL_ID);
    }
}