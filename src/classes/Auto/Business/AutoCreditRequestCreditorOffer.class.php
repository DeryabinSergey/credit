<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-02-20 01:47:49                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoCreditRequestCreditorOffer extends IdentifiableObject implements Created, SecurityObject
	{
		protected $request = null;
		protected $requestId = null;
		protected $status = null;
		protected $statusId = null;
		protected $createdTime = null;
		protected $summ = null;
		protected $minPeriod = null;
		protected $maxPeriod = null;
		protected $percents = null;
		protected $percentsOnly = false;
		protected $address = null;
		protected $date = null;
		protected $time = null;
		protected $text = null;
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function getRequest()
		{
			if (!$this->request && $this->requestId) {
				$this->request = CreditRequestCreditor::dao()->getById($this->requestId);
			}
			
			return $this->request;
		}
		
		public function getRequestId()
		{
			return $this->request
				? $this->request->getId()
				: $this->requestId;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setRequest(CreditRequestCreditor $request)
		{
			$this->request = $request;
			$this->requestId = $request ? $request->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setRequestId($id)
		{
			$this->request = null;
			$this->requestId = $id;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function dropRequest()
		{
			$this->request = null;
			$this->requestId = null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorOfferStatus
		**/
		public function getStatus()
		{
			if (!$this->status && $this->statusId) {
				$this->status = new CreditRequestCreditorOfferStatus($this->statusId);
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
		 * @return CreditRequestCreditorOffer
		**/
		public function setStatus(CreditRequestCreditorOfferStatus $status)
		{
			$this->status = $status;
			$this->statusId = $status ? $status->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setStatusId($id)
		{
			$this->status = null;
			$this->statusId = $id;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function dropStatus()
		{
			$this->status = null;
			$this->statusId = null;
			
			return $this;
		}
		
		/**
		 * @return Timestamp
		**/
		public function getCreatedTime()
		{
			return $this->createdTime;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setCreatedTime(Timestamp $createdTime)
		{
			$this->createdTime = $createdTime;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function dropCreatedTime()
		{
			$this->createdTime = null;
			
			return $this;
		}
		
		public function getSumm()
		{
			return $this->summ;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setSumm($summ)
		{
			$this->summ = $summ;
			
			return $this;
		}
		
		public function getMinPeriod()
		{
			return $this->minPeriod;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
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
		 * @return CreditRequestCreditorOffer
		**/
		public function setMaxPeriod($maxPeriod)
		{
			$this->maxPeriod = $maxPeriod;
			
			return $this;
		}
		
		public function getPercents()
		{
			return $this->percents;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setPercents($percents)
		{
			$this->percents = $percents;
			
			return $this;
		}
		
		public function getPercentsOnly()
		{
			return $this->percentsOnly;
		}
		
		public function isPercentsOnly()
		{
			return $this->percentsOnly;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setPercentsOnly($percentsOnly = false)
		{
			$this->percentsOnly = ($percentsOnly === true);
			
			return $this;
		}
		
		public function getAddress()
		{
			return $this->address;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setAddress($address)
		{
			$this->address = $address;
			
			return $this;
		}
		
		/**
		 * @return Date
		**/
		public function getDate()
		{
			return $this->date;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setDate(Date $date = null)
		{
			$this->date = $date;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function dropDate()
		{
			$this->date = null;
			
			return $this;
		}
		
		/**
		 * @return Time
		**/
		public function getTime()
		{
			return $this->time;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setTime(Time $time = null)
		{
			$this->time = $time;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function dropTime()
		{
			$this->time = null;
			
			return $this;
		}
		
		public function getText()
		{
			return $this->text;
		}
		
		/**
		 * @return CreditRequestCreditorOffer
		**/
		public function setText($text)
		{
			$this->text = $text;
			
			return $this;
		}
	}
?>