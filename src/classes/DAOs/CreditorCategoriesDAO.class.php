<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-08 18:33:13                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class CreditorCategoriesDAO extends OneToManyLinked
	{
		public function __construct(Creditor $creditor, $lazy = false)
		{
			parent::__construct(
				$creditor,
				CreditorCategory::dao(),
				$lazy
			);
		}
		
		/**
		 * @return CreditorCategoriesDAO
		**/
		public static function create(Creditor $creditor, $lazy = false)
		{
			return new self($creditor, $lazy);
		}
		
		public function getParentIdField()
		{
			return 'creditor_id';
		}
	}
?>