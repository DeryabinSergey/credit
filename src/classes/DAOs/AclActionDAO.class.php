<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 14:57:08                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class AclActionDAO extends AutoAclActionDAO
	{
		public function getByAction($action) 
		{
                    return $this->getByLogic(Expression::eq('action', $action), Cache::EXPIRES_FOREVER);
		}
	}
?>