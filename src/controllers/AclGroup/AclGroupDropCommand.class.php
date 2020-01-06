<?

class AclGroupDropCommand extends DropCommand implements SecurityCommand
{
    const ERROR_EXTERNAL = 0x0003;
    const ERROR_INTERNAL = 0x0004;

    /**
     * @return AclGroupDropCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = ModelAndView::create();
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        
        if ($form->getValue('ok')) {
            try {
                $tr = InnerTransaction::begin($subject->dao());                  
                if ($form->getValue('id') instanceof AclGroup) {
                    $form->getValue('id')->getRights()->dropList();
                }
                $mav = parent::run($subject, $form, $request);
                $tr->commit();
            } catch(DatabaseException $e) {
                $tr->rollback();
                $form->markCustom('id', self::ERROR_EXTERNAL);
            } 
        }
        
        if ($mav->getView() != BaseEditor::COMMAND_SUCCEEDED) {
            
            $userList = array();
            $criteria = Criteria::create(User::dao())->
                add(Expression::eq('group', $form->getValue('id')->getId()))->
                addOrder(OrderBy::create('login')->asc());
            
            try {
                $userList = $criteria->getList();
            } catch(ObjectNotFoundException $e) { }
            
            $mav->
                getModel()->
                    set('userList', $userList);
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
        return SecurityManager::isAllowedAction(AclAction::DELETE_ACTION, AclContext::ACL_ID);
    }
}