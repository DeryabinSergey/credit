<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2019-12-26 13:54:24                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoUser extends NamedObject
	{
		protected $email = null;
		protected $password = null;
		
		public function getEmail()
		{
			return $this->email;
		}
		
		/**
		 * @return User
		**/
		public function setEmail($email)
		{
			$this->email = $email;
			
			return $this;
		}
		
		public function getPassword()
		{
			return $this->password;
		}
		
		/**
		 * @return User
		**/
		public function setPassword($password)
		{
			$this->password = $password;
			
			return $this;
		}
	}
?>