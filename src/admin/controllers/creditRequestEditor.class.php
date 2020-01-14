<?php

class creditRequestEditor extends baseCreditRequestEditor
{  
    const ACTION_VIEW   = 'view';
    
    public function __construct()
    {
        $this->insertCommand(self::ACTION_RESTORE, CreditRequestRestoreCommand::create());
        $this->insertCommand(self::ACTION_VIEW, CreditRequestViewCommand::create());

        parent::__construct(CreditRequest::create());
    }
    
}