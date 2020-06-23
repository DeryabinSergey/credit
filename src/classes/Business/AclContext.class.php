<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 15:34:44                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class AclContext extends AutoAclContext implements Prototyped, DAOConnected
	{
            const ACL_ID                            = 1; // права на сайте    
            const CONTROL_PANEL_ID                  = 2; // панель управления
            const USER_ID                           = 3; // пользователи сайта
            const INVESTOR_OFFER_ID                 = 4; // предложения пользователей
            const CATEGORY_ID                       = 5; // категории объявлений
            const CREDITOR_ID                       = 6; // кредиторы
            const CREDIT_REQUEST_ID                 = 7; // запросы на кредит от пользователей
            const CREDIT_REQUEST_CREDITOR           = 8; //
            const CREDIT_REQUEST_CREDITOR_OFFER     = 9; //
	    const NEWS_ID			    = 10; // новости
            
            /**
             * @return AclContext
            **/
            public static function create()
            {
                    return new self;
            }

            /**
             * @return AclContextDAO
            **/
            public static function dao()
            {
                    return Singleton::getInstance('AclContextDAO');
            }

            /**
             * @return ProtoAclContext
            **/
            public static function proto()
            {
                    return Singleton::getInstance('ProtoAclContext');
            }

            // your brilliant stuff goes here
	}
?>