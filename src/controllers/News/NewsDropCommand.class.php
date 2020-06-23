<?

class NewsDropCommand extends DropCommand implements SecurityCommand
{    
    const ERROR_EXTERNAL = 0x0003;
    
    /**
     * @return NewsDropCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
            
        if ($process) {

            try {
                $mav = parent::run($subject, $form, $request);
            } catch(DatabaseException $e) {
                $form->markCustom('id', self::ERROR_EXTERNAL);
            }
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            add(Primitive::boolean('ok'))->
            drop('createdDate')->
            drop('sid')->
            drop('type')->
            drop('title')->
            drop('description')->
            drop('text');
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::DELETE_ACTION, AclContext::NEWS_ID);
    }

}