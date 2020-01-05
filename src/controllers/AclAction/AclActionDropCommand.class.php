<?

class AclActionDropCommand extends DropCommand implements SecurityCommand
{
    const ERROR_EXTERNAL = 0x0003;

    /**
     * @return AclActionDropCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = ModelAndView::create();
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';

        if ($form->getValue('ok')) {
            try {
                $mav = parent::run($subject, $form, $request);
            } catch(DatabaseException $e) {
                $form->markCustom('id', self::ERROR_EXTERNAL);
            }
        }
        
        if ($mav->getView() != EditorController::COMMAND_SUCCEEDED) {
            
            $rightList = array();
            
            try {
                $rightList = Criteria::create(AclRight::dao())->add(Expression::eq('action', $form->getValue('id')->getId()))->getList();
            } catch(ObjectNotFoundException $e) { }
            
            $mav->
                getModel()->
                    set('rightList', $rightList);
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            add(Primitive::boolean('ok'));
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::DELETE_ACTION, AclContext::ACL_ID);
    }
}