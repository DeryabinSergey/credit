<?php

class newsItem extends baseFront
{    
    protected $news = null;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->form->add(Primitive::string('sid')->required());
    }
    
    public function initVars()
    {
        parent::initVars();
        
        if ($this->form->getValue('sid')) {
	    
	    try {
		$this->news = News::dao()->getByLogic(Expression::eq('sid', $this->form->getValue('sid')));
	    } catch (ObjectNotFoundException $e) { }
            
        }
        
        return $this;
    }
    
    public function getModel(\HttpRequest $request)
    {
	$model = parent::getModel($request);
	
	if (!$this->news instanceof News) {
	    $this->errorView(HttpStatus::CODE_404);
	}
	
	if ($this->isDisplayView()) {

            $newsList = 
		Criteria::create(News::dao())->
		    add(Expression::notEq('id', $this->news->getId()))->
		    setLimit(3)->
		    addOrder(OrderBy::create('id')->desc())->
		    getList();
	    
	    $model->
		set('newsList', $newsList)->
		set('newsItem', $this->news);
	    
	}
	
	return $model;
    }
}