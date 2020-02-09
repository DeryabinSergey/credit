<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-02-08 13:31:39                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class CreditRequestImagesDAO extends OneToManyLinked
	{
		public function __construct(CreditRequest $creditRequest, $lazy = false)
		{
			parent::__construct(
				$creditRequest,
				CreditRequestImage::dao(),
				$lazy
			);
		}
		
		/**
		 * @return CreditRequestImagesDAO
		**/
		public static function create(CreditRequest $creditRequest, $lazy = false)
		{
			return new self($creditRequest, $lazy);
		}
		
		public function getParentIdField()
		{
			return 'owner_id';
		}
	}
?>