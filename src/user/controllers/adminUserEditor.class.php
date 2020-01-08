<?php

class adminUserEditor extends CommandLogger implements UserController
{        
    /**
     * @return adminUserEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_UPDATE, UserUpdateCommand::create());

        $this->defaultAction = self::ACTION_UPDATE;

        parent::__construct(User::create());
    }

    /**
     * @param HttpRequest $request
     * @return ModelAndView
     */
    public function handleRequest(HttpRequest $request)
    {
        $mav = parent::handleRequest($request);

        if ($this->isDisplayView($mav)) {
            Singleton::getInstance('HTMLMetaManager')->setTitle('Редактор пользователей - Администрирование');
            
            if (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_UPDATE))) {
                Singleton::getInstance('HTMLMetaManager')->appendJavaScript('/i/admin-user-editor.js');
            }            
        }

        return $mav;
    }

    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeFrontUrl('adminUserList');
    }
}