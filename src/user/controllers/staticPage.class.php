<?php

class staticPage extends baseFront
{    
    protected $page = null;
    
    protected $part = 'static';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form->add(Primitive::string('content')->required());
    }
    
    public function initVars()
    {
        parent::initVars();
	
	$page = $this->form->getValue('content');
        
        if ($page) {
	    
	    $template = explode("-", $page);
            array_walk($template, function(&$item, $list) { $item = ucfirst($item); });
	    
	    if (file_exists(PATH_TEMPLATES . $this->part . implode("", $template) . EXT_TPL)) {
		$this->page = $page;
		$this->view = $this->part . implode("", $template);
	    }
        }
	
	if (!$this->page) {
	    $this->errorView(HttpStatus::CODE_404);
	}
        
        return $this;
    }
}