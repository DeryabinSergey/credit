<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-05 14:55:03                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoAclGroupRight extends IdentifiableObject
	{
		protected $right = null;
		protected $rightId = null;
		protected $group = null;
		protected $groupId = null;
		
		/**
		 * @return AclRight
		**/
		public function getRight()
		{
			if (!$this->right && $this->rightId) {
				$this->right = AclRight::dao()->getById($this->rightId);
			}
			
			return $this->right;
		}
		
		public function getRightId()
		{
			return $this->right
				? $this->right->getId()
				: $this->rightId;
		}
		
		/**
		 * @return AclGroupRight
		**/
		public function setRight(AclRight $right)
		{
			$this->right = $right;
			$this->rightId = $right ? $right->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return AclGroupRight
		**/
		public function setRightId($id)
		{
			$this->right = null;
			$this->rightId = $id;
			
			return $this;
		}
		
		/**
		 * @return AclGroupRight
		**/
		public function dropRight()
		{
			$this->right = null;
			$this->rightId = null;
			
			return $this;
		}
		
		/**
		 * @return AclGroup
		**/
		public function getGroup()
		{
			if (!$this->group && $this->groupId) {
				$this->group = AclGroup::dao()->getById($this->groupId);
			}
			
			return $this->group;
		}
		
		public function getGroupId()
		{
			return $this->group
				? $this->group->getId()
				: $this->groupId;
		}
		
		/**
		 * @return AclGroupRight
		**/
		public function setGroup(AclGroup $group)
		{
			$this->group = $group;
			$this->groupId = $group ? $group->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return AclGroupRight
		**/
		public function setGroupId($id)
		{
			$this->group = null;
			$this->groupId = $id;
			
			return $this;
		}
		
		/**
		 * @return AclGroupRight
		**/
		public function dropGroup()
		{
			$this->group = null;
			$this->groupId = null;
			
			return $this;
		}
	}
?>