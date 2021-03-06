<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-05 14:55:03                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class AclGroup extends AutoAclGroup implements Prototyped, DAOConnected
	{
		/**
		 * @return AclGroup
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return AclGroupDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('AclGroupDAO');
		}
		
		/**
		 * @return ProtoAclGroup
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoAclGroup');
		}
		
		// your brilliant stuff goes here
	}
?>