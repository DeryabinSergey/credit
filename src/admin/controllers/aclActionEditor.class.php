<?php

class aclActionEditor extends CommandLogger implements UserController
{    
    /**
     * @return aclActionEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_ADD, AclActionAddCommand::create());
        $this->insertCommand(self::ACTION_DELETE, AclActionDropCommand::create());
        $this->insertCommand(self::ACTION_UPDATE, AclActionUpdateCommand::create());

        $this->defaultAction = self::ACTION_ADD;

        parent::__construct(AclActionEdited::create());
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
                setTitle('Редактор действий прав - Администрирование');
        }

        return $mav;
    }

    /**
     * @param ModelAndView $mav
     * @param HttpRequest $request
     * @return ModelAndView
     */
    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {
        if ($mav->getView() == self::COMMAND_SUCCEEDED) {
            Cache::worker(AclAction::dao())->uncacheLists();
        }

        return parent::postHandleRequest($mav, $request);
    }

    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeUrl('aclActionList');
    }
}