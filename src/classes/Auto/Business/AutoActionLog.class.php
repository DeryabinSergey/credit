<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 15:15:32                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoActionLog extends IdentifiableObject
	{
		protected $createdTime = null;
		protected $user = null;
		protected $userId = null;
		protected $ip = null;
		protected $sid = null;
		protected $action = null;
		protected $objectName = null;
		protected $objectId = null;
		
		/**
		 * @return Timestamp
		**/
		public function getCreatedTime()
		{
			return $this->createdTime;
		}
		
		/**
		 * @return ActionLog
		**/
		public function setCreatedTime(Timestamp $createdTime)
		{
			$this->createdTime = $createdTime;
			
			return $this;
		}
		
		/**
		 * @return ActionLog
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
		 * @return ActionLog
		**/
		public function setUser(User $user)
		{
			$this->user = $user;
			$this->userId = $user ? $user->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return ActionLog
		**/
		public function setUserId($id)
		{
			$this->user = null;
			$this->userId = $id;
			
			return $this;
		}
		
		/**
		 * @return ActionLog
		**/
		public function dropUser()
		{
			$this->user = null;
			$this->userId = null;
			
			return $this;
		}
		
		public function getIp()
		{
			return $this->ip;
		}
		
		/**
		 * @return ActionLog
		**/
		public function setIp($ip)
		{
			$this->ip = $ip;
			
			return $this;
		}
		
		public function getSid()
		{
			return $this->sid;
		}
		
		/**
		 * @return ActionLog
		**/
		public function setSid($sid)
		{
			$this->sid = $sid;
			
			return $this;
		}
		
		public function getAction()
		{
			return $this->action;
		}
		
		/**
		 * @return ActionLog
		**/
		public function setAction($action)
		{
			$this->action = $action;
			
			return $this;
		}
		
		public function getObjectName()
		{
			return $this->objectName;
		}
		
		/**
		 * @return ActionLog
		**/
		public function setObjectName($objectName)
		{
			$this->objectName = $objectName;
			
			return $this;
		}
		
		public function getObjectId()
		{
			return $this->objectId;
		}
		
		/**
		 * @return ActionLog
		**/
		public function setObjectId($objectId)
		{
			$this->objectId = $objectId;
			
			return $this;
		}
	}
?>