<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-02-11 03:06:09                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

final class CreditRequestCreditor extends AutoCreditRequestCreditor implements Prototyped, DAOConnected
{
    protected $hasAcceptedOffers = null;

    /**
     * @return CreditRequestCreditor
    **/
    public static function create()
    {
            return new self;
    }

    /**
     * @return CreditRequestCreditorDAO
    **/
    public static function dao()
    {
            return Singleton::getInstance('CreditRequestCreditorDAO');
    }

    /**
     * @return ProtoCreditRequestCreditor
    **/
    public static function proto()
    {
            return Singleton::getInstance('ProtoCreditRequestCreditor');
    }

    /**
     * Метод позволяет определить есть ли у кредитной организации принятые кредитные предложения.
     * @return boolean
     */
    public function hasAcceptedOffers()
    {
        if (is_null($this->hasAcceptedOffers)) {
            $hasAccepted = false;

            if ($this->getOffers()->isFetched() && $this->getOffers()->getCriteria()) {
                $this->getOffers()->setCriteria(Criteria::create())->clean();
            }
            
            foreach($this->getOffers()->getList() as $offer) {
                $hasAccepted = $hasAccepted || in_array($offer->getStatus()->getId(), array(CreditRequestCreditorOfferStatus::TYPE_ACCEPTED));

                if ($hasAccepted) break;
            }
            
            $this->hasAcceptedOffers = $hasAccepted;
        }

        return $this->hasAcceptedOffers;
    }

    /**
     * Метод позволяет определить есть ли у кредитной организации предложения где назначена встреча.
     * @return boolean
     */
    public function hasMeetingOffers()
    {
        if (is_null($this->hasAcceptedOffers)) {
            $hasAccepted = false;

            if ($this->getOffers()->isFetched() && $this->getOffers()->getCriteria()) {
                $this->getOffers()->setCriteria(Criteria::create())->clean();
            }
            
            foreach($this->getOffers()->getList() as $offer) {
                $hasAccepted = $hasAccepted || in_array($offer->getStatus()->getId(), array(CreditRequestCreditorOfferStatus::TYPE_MEETING));

                if ($hasAccepted) break;
            }
            
            $this->hasAcceptedOffers = $hasAccepted;
        }

        return $this->hasAcceptedOffers;
    }
}