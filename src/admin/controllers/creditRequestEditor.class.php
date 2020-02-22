<?php

class creditRequestEditor extends baseCreditRequestEditor
{      
    public function __construct()
    {
        $this->insertCommand(self::ACTION_RESTORE, CreditRequestRestoreCommand::create());

        parent::__construct(CreditRequest::create());
    }
    
}