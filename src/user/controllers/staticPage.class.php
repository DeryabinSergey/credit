<?php

class staticPage extends baseFront
{
    protected 
        $pages = 
            array(
                'about' => 'Информация о портале',
                'privacy-policy' => 'Политика обработки персональных данных',
                'contacts' => 'Связаться с нами',
                'credit-pod-zalog-avto' => 'Кредит под залог авто'
            );
    
    protected $page = null;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form->add(Primitive::string('content')->required());
    }
    
    public function initVars()
    {
        parent::initVars();
        
        if (!$this->form->getValue('content') || !isset($this->pages[$this->form->getValue('content')])) {
            $this->errorView(HttpStatus::CODE_404);
        } else {
            $this->page = $this->form->getValue('content');
        }
        
        return $this;
    }
    
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle($this->pages[$this->page]);
            $template = explode("-", $this->page);
            array_walk($template, function(&$item, $list) { $item = ucfirst($item); });
            $this->view = "static".implode("", $template);
            
        }
        
        return $model;
    }
}