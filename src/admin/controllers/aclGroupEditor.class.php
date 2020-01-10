<?php

class aclGroupEditor extends CommandLogger implements UserController
{
    /**
     * @return aclGroupEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_ADD, AclGroupAddCommand::create());
        $this->insertCommand(self::ACTION_UPDATE, AclGroupUpdateCommand::create());
        $this->insertCommand(self::ACTION_DELETE, AclGroupDropCommand::create());

        $this->defaultAction = self::ACTION_ADD;

        parent::__construct(AclGroup::create());
    }

    /**
     * @param HttpRequest $request
     * @return ModelAndView
     */
    public function handleRequest(HttpRequest $request)
    {
        $mav = parent::handleRequest($request);

        if ($this->isDisplayView($mav)) {
            Singleton::getInstance('HTMLMetaManager')->
                setTitle('Редактор групп - Администрирование');
            
            if (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_ADD, self::ACTION_UPDATE))) {
                Singleton::getInstance('HTMLMetaManager')->appendJavaScript('/i/acl-group-editor.js');
            }
        }

        return $mav;
    }

    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeFrontUrl('aclGroupList');
    }
}