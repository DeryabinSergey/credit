<?

class NewsUpdateCommand extends SaveCommand implements SecurityCommand
{   
    /**
     * @return NewsUpdateCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();

        if ($process) {

            try {
                $mav = parent::run($subject, $form, $request);
            } catch(Exception $e) {
                error_log("Ошибка при редактировании новости: {$e->getMessage()}\n{$e->getTraceAsString()}");
                $form->markCustom('id', self::ERROR_INTERNAL);
                if ($mav->getView() == EditorController::COMMAND_SUCCEEDED) {
                    $mav->setView(EditorController::COMMAND_FAILED);
                }
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
	    drop('preview')->
            get('title')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars());
        
        $form->
            get('description')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars());
	
	$form->
            get('text')->
                addImportFilter(
                    FilterChain::create()->
                        add(Filter::pcre()->setExpression("/<p[^>]*>(&nbsp;|\s|)*<\/p>(\\r?\\n)?/iu", ""))->
                        add(Filter::pcre()->setExpression("/<(div|span|font|h1|h2|h3|h4|h5|h6)>(&nbsp;|\s|)*<\/\\1>(\\r?\\n)?/iu", ""))->
                        add(Filter::trim())
                );
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::NEWS_ID);
    }

}