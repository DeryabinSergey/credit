<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 21:59:12                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class AuthLog extends AutoAuthLog implements Prototyped, DAOConnected
	{
		/**
		 * @return AuthLog
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return AuthLogDAO
		**/
		public static function dao()
		{
			return Singleton::getInstance('AuthLogDAO');
		}
		
		/**
		 * @return ProtoAuthLog
		**/
		public static function proto()
		{
			return Singleton::getInstance('ProtoAuthLog');
		}

		public function getRealIp()
                {
                    return long2ip($this->getIp());
                }

                public function setRealIp($ip)
                {
                    return $this->setIp(ip2long($ip));
                }
	}
?>