<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-02-25 01:26:34                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoCreditRequestCreditor extends IdentifiableObject implements Created
	{
		protected $createdTime = null;
		protected $request = null;
		protected $requestId = null;
		protected $creditor = null;
		protected $creditorId = null;
		protected $status = null;
		protected $statusId = null;
		protected $expired = null;
		protected $investSumm = null;
		protected $investPeriod = null;
		protected $investPercents = null;
		protected $investNotified = null;
		protected $offers = null;
		
		/**
		 * @return Timestamp
		**/
		public function getCreatedTime()
		{
			return $this->createdTime;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setCreatedTime(Timestamp $createdTime)
		{
			$this->createdTime = $createdTime;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function dropCreatedTime()
		{
			$this->createdTime = null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequest
		**/
		public function getRequest()
		{
			if (!$this->request && $this->requestId) {
				$this->request = CreditRequest::dao()->getById($this->requestId);
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
		 * @return CreditRequestCreditor
		**/
		public function setRequest(CreditRequest $request)
		{
			$this->request = $request;
			$this->requestId = $request ? $request->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setRequestId($id)
		{
			$this->request = null;
			$this->requestId = $id;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function dropRequest()
		{
			$this->request = null;
			$this->requestId = null;
			
			return $this;
		}
		
		/**
		 * @return Creditor
		**/
		public function getCreditor()
		{
			if (!$this->creditor && $this->creditorId) {
				$this->creditor = Creditor::dao()->getById($this->creditorId);
			}
			
			return $this->creditor;
		}
		
		public function getCreditorId()
		{
			return $this->creditor
				? $this->creditor->getId()
				: $this->creditorId;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setCreditor(Creditor $creditor)
		{
			$this->creditor = $creditor;
			$this->creditorId = $creditor ? $creditor->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setCreditorId($id)
		{
			$this->creditor = null;
			$this->creditorId = $id;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function dropCreditor()
		{
			$this->creditor = null;
			$this->creditorId = null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorStatus
		**/
		public function getStatus()
		{
			if (!$this->status && $this->statusId) {
				$this->status = new CreditRequestCreditorStatus($this->statusId);
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
		 * @return CreditRequestCreditor
		**/
		public function setStatus(CreditRequestCreditorStatus $status)
		{
			$this->status = $status;
			$this->statusId = $status ? $status->getId() : null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setStatusId($id)
		{
			$this->status = null;
			$this->statusId = $id;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditor
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
		public function getExpired()
		{
			return $this->expired;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setExpired(Timestamp $expired = null)
		{
			$this->expired = $expired;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function dropExpired()
		{
			$this->expired = null;
			
			return $this;
		}
		
		public function getInvestSumm()
		{
			return $this->investSumm;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setInvestSumm($investSumm)
		{
			$this->investSumm = $investSumm;
			
			return $this;
		}
		
		public function getInvestPeriod()
		{
			return $this->investPeriod;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setInvestPeriod($investPeriod)
		{
			$this->investPeriod = $investPeriod;
			
			return $this;
		}
		
		public function getInvestPercents()
		{
			return $this->investPercents;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setInvestPercents($investPercents)
		{
			$this->investPercents = $investPercents;
			
			return $this;
		}
		
		/**
		 * @return Timestamp
		**/
		public function getInvestNotified()
		{
			return $this->investNotified;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function setInvestNotified(Timestamp $investNotified = null)
		{
			$this->investNotified = $investNotified;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function dropInvestNotified()
		{
			$this->investNotified = null;
			
			return $this;
		}
		
		/**
		 * @return CreditRequestCreditorOffersDAO
		**/
		public function getOffers($lazy = false)
		{
			if (!$this->offers || ($this->offers->isLazy() != $lazy)) {
				$this->offers = new CreditRequestCreditorOffersDAO($this, $lazy);
			}
			
			return $this->offers;
		}
		
		/**
		 * @return CreditRequestCreditor
		**/
		public function fillOffers($collection, $lazy = false)
		{
			$this->offers = new CreditRequestCreditorOffersDAO($this, $lazy);
			
			if (!$this->id) {
				throw new WrongStateException(
					'i do not know which object i belong to'
				);
			}
			
			$this->offers->mergeList($collection);
			
			return $this;
		}
	}
?>