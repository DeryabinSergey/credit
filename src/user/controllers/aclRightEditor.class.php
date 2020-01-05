<?php

class aclRightEditor extends CommandLogger implements UserController
{    
    /**
     * @return aclRightEditor
     */
    public static function create()
    {
        return new self;
    }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_ADD, AclRightAddCommand::create());
        $this->insertCommand(self::ACTION_DELETE, AclRightDropCommand::create());
        $this->insertCommand(self::ACTION_UPDATE, AclRightUpdateCommand::create());

        $this->defaultAction = self::ACTION_ADD;

        parent::__construct(AclRightEdited::create());
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
                setTitle('Редактор прав - Администрирование');
        }

        return $mav;
    }

    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeFrontUrl('aclRightList');
    }
}