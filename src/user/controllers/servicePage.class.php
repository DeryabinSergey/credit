<?php

class servicePage extends staticPage
{    
    public function __construct()
    {
        parent::__construct();
        
        $this->part = 'service';
    }
}