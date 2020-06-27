<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-06-26 14:23:20                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoNews extends IdentifiableObject implements PreviewPicture, PreviewMediumPicture, PreviewBigPicture
	{
		protected $sid = null;
		protected $title = null;
		protected $createdDate = null;
		protected $description = null;
		protected $text = null;
		protected $type = null;
		protected $preview = null;
		
		public function getSid()
		{
			return $this->sid;
		}
		
		/**
		 * @return News
		**/
		public function setSid($sid)
		{
			$this->sid = $sid;
			
			return $this;
		}
		
		public function getTitle()
		{
			return $this->title;
		}
		
		/**
		 * @return News
		**/
		public function setTitle($title)
		{
			$this->title = $title;
			
			return $this;
		}
		
		/**
		 * @return Date
		**/
		public function getCreatedDate()
		{
			return $this->createdDate;
		}
		
		/**
		 * @return News
		**/
		public function setCreatedDate(Date $createdDate)
		{
			$this->createdDate = $createdDate;
			
			return $this;
		}
		
		/**
		 * @return News
		**/
		public function dropCreatedDate()
		{
			$this->createdDate = null;
			
			return $this;
		}
		
		public function getDescription()
		{
			return $this->description;
		}
		
		/**
		 * @return News
		**/
		public function setDescription($description)
		{
			$this->description = $description;
			
			return $this;
		}
		
		public function getText()
		{
			return $this->text;
		}
		
		/**
		 * @return News
		**/
		public function setText($text)
		{
			$this->text = $text;
			
			return $this;
		}
		
		/**
		 * @return NewsType
		**/
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return News
		**/
		public function setType(NewsType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		/**
		 * @return News
		**/
		public function dropType()
		{
			$this->type = null;
			
			return $this;
		}
		
		/**
		 * @return ImageType
		**/
		public function getPreview()
		{
			return $this->preview;
		}
		
		/**
		 * @return News
		**/
		public function setPreview(ImageType $preview = null)
		{
			$this->preview = $preview;
			
			return $this;
		}
		
		/**
		 * @return News
		**/
		public function dropPreview()
		{
			$this->preview = null;
			
			return $this;
		}
	}
?>