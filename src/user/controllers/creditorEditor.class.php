<?php

class creditorEditor extends CommandLogger implements UserController
{    
    const ACTION_PUBLISH    = 'publish';
    
    /**
     * @return creditorEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_ADD, CreditorAddCommand::create());
        $this->insertCommand(self::ACTION_DELETE, CreditorDropCommand::create());
        $this->insertCommand(self::ACTION_RESTORE, CreditorRestoreCommand::create());
        $this->insertCommand(self::ACTION_PUBLISH, CreditorPublishCommand::create());
        $this->insertCommand(self::ACTION_UPDATE, CreditorUpdateCommand::create());

        $this->defaultAction = self::ACTION_ADD;

        parent::__construct(Creditor::create());
    }

    /**
     * @param HttpRequest $request
     * @return ModelAndView
     */
    public function handleRequest(HttpRequest $request)
    {
        $mav = parent::handleRequest($request);

        if ($this->isDisplayView($mav)) {
            Singleton::getInstance('HTMLMetaManager')->setTitle('Редактор компаний - Панель Управления');
            
            if (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_ADD, self::ACTION_UPDATE, self::ACTION_PUBLISH))) {
                Singleton::getInstance('HTMLMetaManager')->                     
                    appendJavaScript('/i/creditor-editor.js');
            }            
        }

        return $mav;
    }

    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeFrontUrl('creditorList');
    }
}