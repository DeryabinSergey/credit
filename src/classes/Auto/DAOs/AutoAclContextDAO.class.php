<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 15:34:44                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoAclContextDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'acl_context';
		}
		
		public function getObjectName()
		{
			return 'AclContext';
		}
		
		public function getSequence()
		{
			return 'acl_context_id';
		}
	}
?>