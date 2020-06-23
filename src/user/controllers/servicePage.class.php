<?php

class servicePage extends staticPage
{    
    public function __construct()
    {
        parent::__construct();
        
        $this->part = 'service';
    }
    
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            $newsList = 
		Criteria::create(News::dao())->
		    setLimit(3)->
		    addOrder(OrderBy::create('id')->desc())->
		    getList();
	    
	    $model->set('newsList', $newsList);
        }
        
        return $model;
    }
}