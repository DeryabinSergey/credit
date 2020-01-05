<?

class AclContextAddCommand extends ImportCommand implements SecurityCommand
{    
    const ERROR_DUPLICATE = 0x0003;
    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
            
        if ($process) {
            $subject->setId($form->getValue('newId'));

            try {
                $mav = parent::run($subject, $form, $request);
            } catch(DuplicateObjectException $e) {
                if (mb_stripos($e->getMessage(), AclContextDAO::UQ_ACL_CONTEXT) !== false) {
                    $form->markCustom('name', self::ERROR_DUPLICATE);
                } else {
                    $form->markCustom('newId', self::ERROR_DUPLICATE);
                }
            }
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            add(Primitive::integer('newId')->setMin(0)->required())->
            get('name')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars());
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::ADD_ACTION, AclContext::ACL_ID);
    }

}