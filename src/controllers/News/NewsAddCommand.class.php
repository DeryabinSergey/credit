<?

class NewsAddCommand extends AddCommand implements SecurityCommand
{    
    const ERROR_DUPLICATE = 0x0003;
    
    /**
     * @return NewsAddCommand
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
                $form->markCustom('id', self::ERROR_DUPLICATE);
            }
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            drop('createdDate')->
            drop('sid')->
            drop('type')->
            get('title')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars());
        
        $form->
            get('description')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars());
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::ADD_ACTION, AclContext::NEWS_ID);
    }

}