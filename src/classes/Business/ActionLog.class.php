<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 15:15:32                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	class ActionLog extends AutoActionLog implements Prototyped, DAOConnected
	{
            /**
             * @return ActionLog
            **/
            public static function create()
            {
                    return new self;
            }

            /**
             * @return ActionLogDAO
            **/
            public static function dao()
            {
                    return Singleton::getInstance('ActionLogDAO');
            }

            /**
             * @return ProtoActionLog
            **/
            public static function proto()
            {
                    return Singleton::getInstance('ProtoActionLog');
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