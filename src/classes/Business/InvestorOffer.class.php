<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 14:40:34                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class InvestorOffer extends AutoInvestorOffer implements Prototyped, DAOConnected
	{
		/**
		 * @return InvestorOffer
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return InvestorOfferDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('InvestorOfferDAO');
		}
		
		/**
		 * @return ProtoInvestorOffer
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoInvestorOffer');
		}
		
		public function checkPermissions($label)
                {
                    return 
                        ($label == AclAction::DELETE_ACTION && !$this->isDeleted() && ((SecurityManager::isAuth() && $this->getUser()->getId() == SecurityManager::getUser()->getId()) || SecurityManager::isAllowedAction($label, AclContext::INVESTOR_OFFER_ID))) ||
                        ($label == AclAction::RESTORE_ACTION && $this->isDeleted() && SecurityManager::isAllowedAction($label, AclContext::INVESTOR_OFFER_ID)) ||
                        ($label == AclAction::PUBLISH_ACTION && !$this->isActive() && SecurityManager::isAllowedAction($label, AclContext::INVESTOR_OFFER_ID)) ||
                        (
                            $label != AclAction::DELETE_ACTION &&
                            $label != AclAction::RESTORE_ACTION &&
                            $label != AclAction::PUBLISH_ACTION
                        );
                }
	}
?>