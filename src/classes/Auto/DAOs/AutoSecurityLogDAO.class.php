<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 22:05:24                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoSecurityLogDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'security_log';
		}
		
		public function getObjectName()
		{
			return 'SecurityLog';
		}
		
		public function getSequence()
		{
			return 'security_log_id';
		}
	}
?>