<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-05 14:55:03                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class AclRight extends AutoAclRight implements Prototyped, DAOConnected
	{
		/**
		 * @return AclRight
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return AclRightDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('AclRightDAO');
		}
		
		/**
		 * @return ProtoAclRight
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoAclRight');
		}
		
		// your brilliant stuff goes here
	}
?>