<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-12 23:19:36                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoCreditRequestDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'credit_request';
		}
		
		public function getObjectName()
		{
			return 'CreditRequest';
		}
		
		public function getSequence()
		{
			return 'credit_request_id';
		}
	}
?>