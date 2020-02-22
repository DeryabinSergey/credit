<?php

class baseCreditRequestCreditorOfferEditor extends CommandLogger implements UserController
{    
    const ACTION_ACCEPT             = 'accept';
    const ACTION_CREDITOR_ACCEPT    = 'caccept';
    const ACTION_MEETING_CANCEL     = 'mcancel';
    const ACTION_CANCEL             = 'canceled';
    const ACTION_REJECT             = 'reject';
    const ACTION_MEETING            = 'meeting';
    
    public function __construct()
    {
        $this->insertCommand(self::ACTION_DELETE, CreditRequestCreditorOfferDropCommand::create());
        $this->insertCommand(self::ACTION_ACCEPT, CreditRequestCreditorOfferAcceptCommand::create());
        $this->insertCommand(self::ACTION_CREDITOR_ACCEPT, CreditRequestCreditorOfferCreditorAcceptCommand::create());
        $this->insertCommand(self::ACTION_CANCEL, CreditRequestCreditorOfferCancelCommand::create());
        $this->insertCommand(self::ACTION_MEETING_CANCEL, CreditRequestCreditorOfferMeetingCancelCommand::create());
        $this->insertCommand(self::ACTION_REJECT, CreditRequestCreditorOfferRejectCommand::create());
        $this->insertCommand(self::ACTION_MEETING, CreditRequestCreditorOfferMeetingCommand::create());

        $this->defaultAction = self::ACTION_DELETE;

        parent::__construct(CreditRequestCreditorOffer::create());
    }
}