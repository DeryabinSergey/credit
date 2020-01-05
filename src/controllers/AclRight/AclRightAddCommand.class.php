<?

class AclRightAddCommand extends AddCommand implements SecurityCommand
{
    const ERROR_DUPLICATE = 0x0003;
    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
            
        if ($process) {
            $form->markGood('id');
            if (!$form->getErrors()) {
                $criteria = Criteria::create(AclRight::dao())->
                    add(Expression::eq('context', $form->getValue('context')->getId()))->
                    add(Expression::eq('action', $form->getValue('aclAction')->getId()));
                if ($form->exists('sectionId')) {
                    if ($form->getValue('sectionId')) {
                        $criteria->add(Expression::eq('sectionId', $form->getValue('sectionId')));
                    } else {
                        $criteria->add(Expression::isNull('sectionId'));
                    }
                }
                try {
                    $criteria->setSilent(false)->getList();
                    $form->markCustom('context', self::ERROR_DUPLICATE);
                } catch(ObjectNotFoundException $e) { }
            }
            
            try {
                $mav = parent::run($subject, $form, $request);
            } catch(DuplicateObjectException $e) {
                $form->markCustom('name', self::ERROR_DUPLICATE);
            }
        }

            if ($mav->getView() != BaseEditor::COMMAND_SUCCEEDED) {
                
                $criteriaContext = Criteria::create(AclContext::dao())->addOrder(OrderBy::create('name')->asc());
                $criteriaAction = Criteria::create(AclAction::dao())->addOrder(OrderBy::create('name')->asc());
                
                $mav->
                    getModel()->
                        set('contextList', $criteriaContext->getList())->
                        set('actionList', $criteriaAction->getList());
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
            drop('sectionId')->
            drop('sectionType');
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::ADD_ACTION, AclContext::ACL_ID);
    }

}