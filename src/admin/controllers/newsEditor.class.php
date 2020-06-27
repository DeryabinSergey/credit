<?php

class newsEditor extends CommandLogger implements UserController
{    
    /**
     * @return newsEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_ADD, NewsAddCommand::create());
        $this->insertCommand(self::ACTION_DELETE, NewsDropCommand::create());
        $this->insertCommand(self::ACTION_UPDATE, NewsUpdateCommand::create());

        $this->defaultAction = self::ACTION_ADD;

        parent::__construct(News::create());
    }

    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {        
        $mav = parent::postHandleRequest($mav, $request);

        if ($this->isDisplayView($mav)) {            

            if (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_ADD, self::ACTION_UPDATE))) {
            
		Singleton::getInstance('HTMLMetaManager')->
		    setTitle('Редактор новостей - Администрирование')->
		    appendJavaScript('/i/tiny/tinymce.min.js')->
                    appendJavaScript('/i/news-editor.js')->
                    appendJavaScript('/i/preview-editor.js');
            }             
        }

        return $mav;
    }

    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeFrontUrl('newsList');
    }
}