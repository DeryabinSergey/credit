<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-05 14:55:03                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoAclRightDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'acl_right';
		}
		
		public function getObjectName()
		{
			return 'AclRight';
		}
		
		public function getSequence()
		{
			return 'acl_right_id';
		}
	}
?>