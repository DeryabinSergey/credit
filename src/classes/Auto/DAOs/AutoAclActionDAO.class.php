<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 14:57:08                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoAclActionDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'acl_action';
		}
		
		public function getObjectName()
		{
			return 'AclAction';
		}
		
		public function getSequence()
		{
			return 'acl_action_id';
		}
	}
?>