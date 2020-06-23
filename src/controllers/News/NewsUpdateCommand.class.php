<?

class NewsUpdateCommand extends SaveCommand implements SecurityCommand
{   
    /**
     * @return NewsUpdateCommand
     */
    public static function create() { return new self; }

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
        return SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::NEWS_ID);
    }

}