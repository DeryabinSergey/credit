<?php

class aclContextEditor extends CommandLogger implements UserController
{    
    /**
     * @return aclContextEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_ADD, AclContextAddCommand::create());
        $this->insertCommand(self::ACTION_DELETE, AclContextDropCommand::create());
        $this->insertCommand(self::ACTION_UPDATE, AclContextUpdateCommand::create());

        $this->defaultAction = self::ACTION_ADD;

        parent::__construct(AclContext::create());
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
                setTitle('Редактор контекстов прав - Администрирование');
        }

        return $mav;
    }

    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeFrontUrl('aclContextList');
    }
}