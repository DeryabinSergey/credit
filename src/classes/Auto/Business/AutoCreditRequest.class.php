<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-03-25 13:52:14                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoCreditRequest extends ImageOwner implements Created, SecurityObject
	{
		protected $createdTime = null;
		protected $notifiedTime = null;
		protected $status = null;
		protected $statusId = null;
		protected $user = null;
		protected $userId = null;
		protected $type = null;
		protected $typeId = null;
		protected $deleted = false;
		protected $category = null;
		protected $categoryId = null;
		protected $name = null;
		protected $summ = null;
		protected $birthDate = null;
		protected $profit = null;
		protected $text = null;
		protected $passport = null;
		protected $ogrn = null;
		protected $images = null;
		protected $creditorRequests = null;
		
		/**
		 * @return Timestamp
		**/
		public function getCreatedTime()
		{
			return $this->createdTime;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setCreatedTime(Timestamp $createdTime)
		{
			$this->createdTime = $createdTime;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function dropCreatedTime()
		{
			$this->createdTime = null;
			
			return $this;
		}
		
		/**
		 * @return Timestamp
		**/
		public function getNotifiedTime()
		{
			return $this->notifiedTime;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setNotifiedTime(Timestamp $notifiedTime = null)
		{
			$this->notifiedTime = $notifiedTime;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function dropNotifiedTime()
		{
			$this->notifiedTime = null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestStatus
		**/
		public function getStatus()
		{
			if (!$this->status && $this->statusId) {
				$this->status = new CreditRequestStatus($this->statusId);
			}
			
			return $this->status;
		}
		
		public function getStatusId()
		{
			return $this->status
				? $this->status->getId()
				: $this->statusId;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setStatus(CreditRequestStatus $status)
		{
			$this->status = $status;
			$this->statusId = $status ? $status->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setStatusId($id)
		{
			$this->status = null;
			$this->statusId = $id;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function dropStatus()
		{
			$this->status = null;
			$this->statusId = null;
			
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
		 * @return CreditRequest
		**/
		public function setUser(User $user)
		{
			$this->user = $user;
			$this->userId = $user ? $user->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setUserId($id)
		{
			$this->user = null;
			$this->userId = $id;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function dropUser()
		{
			$this->user = null;
			$this->userId = null;
			
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
		 * @return CreditRequest
		**/
		public function setType(SubjectType $type)
		{
			$this->type = $type;
			$this->typeId = $type ? $type->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setTypeId($id)
		{
			$this->type = null;
			$this->typeId = $id;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function dropType()
		{
			$this->type = null;
			$this->typeId = null;
			
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
		 * @return CreditRequest
		**/
		public function setDeleted($deleted = false)
		{
			$this->deleted = ($deleted === true);
			
			return $this;
		}
		
		/**
		 * @return Category
		**/
		public function getCategory()
		{
			if (!$this->category && $this->categoryId) {
				$this->category = Category::dao()->getById($this->categoryId);
			}
			
			return $this->category;
		}
		
		public function getCategoryId()
		{
			return $this->category
				? $this->category->getId()
				: $this->categoryId;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setCategory(Category $category = null)
		{
			$this->category = $category;
			$this->categoryId = $category ? $category->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setCategoryId($id = null)
		{
			$this->category = null;
			$this->categoryId = $id;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function dropCategory()
		{
			$this->category = null;
			$this->categoryId = null;
			
			return $this;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public function getSumm()
		{
			return $this->summ;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setSumm($summ)
		{
			$this->summ = $summ;
			
			return $this;
		}
		
		/**
		 * @return Timestamp
		**/
		public function getBirthDate()
		{
			return $this->birthDate;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setBirthDate(Timestamp $birthDate = null)
		{
			$this->birthDate = $birthDate;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function dropBirthDate()
		{
			$this->birthDate = null;
			
			return $this;
		}
		
		public function getProfit()
		{
			return $this->profit;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setProfit($profit)
		{
			$this->profit = $profit;
			
			return $this;
		}
		
		public function getText()
		{
			return $this->text;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setText($text)
		{
			$this->text = $text;
			
			return $this;
		}
		
		public function getPassport()
		{
			return $this->passport;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setPassport($passport)
		{
			$this->passport = $passport;
			
			return $this;
		}
		
		public function getOgrn()
		{
			return $this->ogrn;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function setOgrn($ogrn)
		{
			$this->ogrn = $ogrn;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestImagesDAO
		**/
		public function getImages($lazy = false)
		{
			if (!$this->images || ($this->images->isLazy() != $lazy)) {
				$this->images = new CreditRequestImagesDAO($this, $lazy);
			}
			
			return $this->images;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function fillImages($collection, $lazy = false)
		{
			$this->images = new CreditRequestImagesDAO($this, $lazy);
			
			if (!$this->id) {
				throw new WrongStateException(
					'i do not know which object i belong to'
				);
			}
			
			$this->images->mergeList($collection);
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorRequestsDAO
		**/
		public function getCreditorRequests($lazy = false)
		{
			if (!$this->creditorRequests || ($this->creditorRequests->isLazy() != $lazy)) {
				$this->creditorRequests = new CreditRequestCreditorRequestsDAO($this, $lazy);
			}
			
			return $this->creditorRequests;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function fillCreditorRequests($collection, $lazy = false)
		{
			$this->creditorRequests = new CreditRequestCreditorRequestsDAO($this, $lazy);
			
			if (!$this->id) {
				throw new WrongStateException(
					'i do not know which object i belong to'
				);
			}
			
			$this->creditorRequests->mergeList($collection);
			
			return $this;
		}
	}
?>