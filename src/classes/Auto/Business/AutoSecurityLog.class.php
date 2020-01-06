<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 22:05:24                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoSecurityLog extends IdentifiableObject implements Created
	{
		protected $createdTime = null;
		protected $user = null;
		protected $userId = null;
		protected $sid = null;
		protected $ip = null;
		
		/**
		 * @return Timestamp
		**/
		public function getCreatedTime()
		{
			return $this->createdTime;
		}
		
		/**
		 * @return SecurityLog
		**/
		public function setCreatedTime(Timestamp $createdTime)
		{
			$this->createdTime = $createdTime;
			
			return $this;
		}
		
		/**
		 * @return SecurityLog
		**/
		public function dropCreatedTime()
		{
			$this->createdTime = null;
			
			return $this;
		}
		
		/**
		 * @return User
		**/
		public function getUser()
		{
			if (!$this->user && $this->userId) {
				$this->user = User::dao()->getById($this->userId);
			}
			
			return $this->user;
		}
		
		public function getUserId()
		{
			return $this->user
				? $this->user->getId()
				: $this->userId;
		}
		
		/**
		 * @return SecurityLog
		**/
		public function setUser(User $user)
		{
			$this->user = $user;
			$this->userId = $user ? $user->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return SecurityLog
		**/
		public function setUserId($id)
		{
			$this->user = null;
			$this->userId = $id;
			
			return $this;
		}
		
		/**
		 * @return SecurityLog
		**/
		public function dropUser()
		{
			$this->user = null;
			$this->userId = null;
			
			return $this;
		}
		
		public function getSid()
		{
			return $this->sid;
		}
		
		/**
		 * @return SecurityLog
		**/
		public function setSid($sid)
		{
			$this->sid = $sid;
			
			return $this;
		}
		
		public function getIp()
		{
			return $this->ip;
		}
		
		/**
		 * @return SecurityLog
		**/
		public function setIp($ip)
		{
			$this->ip = $ip;
			
			return $this;
		}
	}
?>