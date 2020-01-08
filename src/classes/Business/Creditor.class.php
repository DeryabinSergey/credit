<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-08 18:33:13                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class Creditor extends AutoCreditor implements Prototyped, DAOConnected
	{
		/**
		 * @return Creditor
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return CreditorDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('CreditorDAO');
		}
		
		/**
		 * @return ProtoCreditor
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoCreditor');
		}
		
		public function checkPermissions($label)
                {
                    return 
                        ($label == AclAction::DELETE_ACTION && !$this->isDeleted() && ((SecurityManager::isAuth() && $this->getUser()->getId() == SecurityManager::getUser()->getId()) || SecurityManager::isAllowedAction($label, AclContext::CREDITOR_ID))) ||
                        ($label == AclAction::EDIT_ACTION && ((SecurityManager::isAuth() && $this->getUser()->getId() == SecurityManager::getUser()->getId()) || SecurityManager::isAllowedAction($label, AclContext::CREDITOR_ID))) ||
                        ($label == AclAction::RESTORE_ACTION && $this->isDeleted() && SecurityManager::isAllowedAction($label, AclContext::CREDITOR_ID)) ||
                        ($label == AclAction::PUBLISH_ACTION && !$this->isActive() && SecurityManager::isAllowedAction($label, AclContext::CREDITOR_ID)) ||
                        (
                            $label != AclAction::DELETE_ACTION &&
                            $label != AclAction::RESTORE_ACTION &&
                            $label != AclAction::PUBLISH_ACTION &&
                            $label != AclAction::EDIT_ACTION
                        );
                }
	}
?>