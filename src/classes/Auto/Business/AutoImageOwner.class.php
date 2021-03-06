<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2019-12-26 13:56:19                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoImageOwner extends IdentifiableObject
	{
		protected $images = null;
		
		/**
		 * @return ImageOwnerImagesDAO
		**/
		public function getImages($lazy = false)
		{
			if (!$this->images || ($this->images->isLazy() != $lazy)) {
				$this->images = new ImageOwnerImagesDAO($this, $lazy);
			}
			
			return $this->images;
		}
		
		/**
		 * @return ImageOwner
		**/
		public function fillImages($collection, $lazy = false)
		{
			$this->images = new ImageOwnerImagesDAO($this, $lazy);
			
			if (!$this->id) {
				throw new WrongStateException(
					'i do not know which object i belong to'
				);
			}
			
			$this->images->mergeList($collection);
			
			return $this;
		}
	}
?>