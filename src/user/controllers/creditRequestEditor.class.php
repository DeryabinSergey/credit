<?php

class creditRequestEditor extends baseCreditRequestEditor
{
    public function __construct()
    {
        parent::__construct(CreditRequest::create());
	
	$this->map->addSource('category', RequestType::get());
    }
}