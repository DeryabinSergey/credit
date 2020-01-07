<?php

class adminCategoryEditor extends CommandLogger implements UserController
{    
    /**
     * @return adminCategoryEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_ADD, CategoryAddCommand::create());
        $this->insertCommand(self::ACTION_DELETE, CategoryDropCommand::create());
        $this->insertCommand(self::ACTION_UPDATE, CategoryUpdateCommand::create());

        $this->defaultAction = self::ACTION_ADD;

        parent::__construct(Category::create());
    }

    /**
     * @param HttpRequest $request
     * @return ModelAndView
     */
    public function handleRequest(HttpRequest $request)
    {
        $mav = parent::handleRequest($request);

        if ($this->isDisplayView($mav)) {
            Singleton::getInstance('HTMLMetaManager')->setTitle('Редактор категорий - Администрирование');
        }

        return $mav;
    }

    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeFrontUrl('adminCategoryList');
    }
}