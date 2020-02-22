<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-02-12 00:00:12                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class UserCreditorsDAO extends OneToManyLinked
	{
		public function __construct(User $user, $lazy = false)
		{
			parent::__construct(
				$user,
				Creditor::dao(),
				$lazy
			);
		}
		
		/**
		 * @return UserCreditorsDAO
		**/
		public static function create(User $user, $lazy = false)
		{
			return new self($user, $lazy);
		}
		
		public function getParentIdField()
		{
			return 'user_id';
		}
	}
?>