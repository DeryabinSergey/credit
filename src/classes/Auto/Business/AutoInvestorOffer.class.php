<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-08 12:23:33                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoInvestorOffer extends IdentifiableObject implements Created, SecurityObject
	{
		protected $createdTime = null;
		protected $type = null;
		protected $typeId = null;
		protected $user = null;
		protected $userId = null;
		protected $active = false;
		protected $deleted = false;
		protected $minPeriod = null;
		protected $maxPeriod = null;
		protected $minSumm = null;
		protected $maxSumm = null;
		protected $percents = null;
		
		/**
		 * @return Timestamp
		**/
		public function getCreatedTime()
		{
			return $this->createdTime;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setCreatedTime(Timestamp $createdTime)
		{
			$this->createdTime = $createdTime;
			
			return $this;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function dropCreatedTime()
		{
			$this->createdTime = null;
			
			return $this;
		}
		
		/**
		 * @return SubjectType
		**/
		public function getType()
		{
			if (!$this->type && $this->typeId) {
				$this->type = new SubjectType($this->typeId);
			}
			
			return $this->type;
		}
		
		public function getTypeId()
		{
			return $this->type
				? $this->type->getId()
				: $this->typeId;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setType(SubjectType $type)
		{
			$this->type = $type;
			$this->typeId = $type ? $type->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setTypeId($id)
		{
			$this->type = null;
			$this->typeId = $id;
			
			return $this;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function dropType()
		{
			$this->type = null;
			$this->typeId = null;
			
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
		 * @return InvestorOffer
		**/
		public function setUser(User $user)
		{
			$this->user = $user;
			$this->userId = $user ? $user->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setUserId($id)
		{
			$this->user = null;
			$this->userId = $id;
			
			return $this;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function dropUser()
		{
			$this->user = null;
			$this->userId = null;
			
			return $this;
		}
		
		public function getActive()
		{
			return $this->active;
		}
		
		public function isActive()
		{
			return $this->active;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setActive($active = false)
		{
			$this->active = ($active === true);
			
			return $this;
		}
		
		public function getDeleted()
		{
			return $this->deleted;
		}
		
		public function isDeleted()
		{
			return $this->deleted;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setDeleted($deleted = false)
		{
			$this->deleted = ($deleted === true);
			
			return $this;
		}
		
		public function getMinPeriod()
		{
			return $this->minPeriod;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setMinPeriod($minPeriod)
		{
			$this->minPeriod = $minPeriod;
			
			return $this;
		}
		
		public function getMaxPeriod()
		{
			return $this->maxPeriod;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setMaxPeriod($maxPeriod)
		{
			$this->maxPeriod = $maxPeriod;
			
			return $this;
		}
		
		public function getMinSumm()
		{
			return $this->minSumm;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setMinSumm($minSumm)
		{
			$this->minSumm = $minSumm;
			
			return $this;
		}
		
		public function getMaxSumm()
		{
			return $this->maxSumm;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setMaxSumm($maxSumm)
		{
			$this->maxSumm = $maxSumm;
			
			return $this;
		}
		
		public function getPercents()
		{
			return $this->percents;
		}
		
		/**
		 * @return InvestorOffer
		**/
		public function setPercents($percents)
		{
			$this->percents = $percents;
			
			return $this;
		}
	}
?>