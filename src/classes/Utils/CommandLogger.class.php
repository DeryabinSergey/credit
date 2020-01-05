<?

abstract class CommandLogger extends CommandContainer
{
    private $notLoggingAction = array();
    
    protected function addNotLoggingAction($action) 
    {
        $this->notLoggingAction[$action] = $action;
        return $this;
    }
    
    /**
     *
     * @param ModelAndView $mav
     * @param HttpRequest $request
     * @return ModelAndView
     */
    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {
        if (
            $this->isDisplayView($mav) &&
            $mav->getView() == BaseEditor::COMMAND_SUCCEEDED &&
            (count($this->notLoggingAction) == 0 || !isset($this->notLoggingAction[$this->getForm()->{$this->getActionMethod()}('action')]))
        ) {
            try {
                ActionLog::dao()->add(
                    ActionLog::create()->
                        setUser(SecurityManager::getUser())->
                        setIp(SecurityManager::getUser()->getIp())->
                        setSid(SecurityManager::getUser()->getSid())->
                        setAction($this->getForm()->{$this->getActionMethod()}('action'))->
                        setObjectName(get_class($this->subject))->
                        setObjectId($mav->getModel()->has('id') ? $mav->getModel()->get('id') : ($this->getForm()->getValue('id') instanceof Identifiable ? $this->getForm()->getValue('id')->getId() : $this->getForm()->getValue('id')))
                );
            } catch(DatabaseException $e) { print_r($e); }
        }
        $mav = parent::postHandleRequest($mav, $request);

        return $mav;
    }
}