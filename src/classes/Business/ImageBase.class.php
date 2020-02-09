<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2019-12-26 13:54:24                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	abstract class ImageBase extends AutoImageBase implements Prototyped
	{                
            public function getFile($thumb = false)
            {
                return ($this instanceof ImageUniqueFileName ? $this->getFileName() : $this->getId()) . ($thumb ? '-t' : '') . '.' . $this->getType()->getExtension();
            }

            public function getPath($thumb = false)
            {
                if ($this->getType() instanceof ImageType)
                    return PictureUtils::getImagePathByObject($this) . $this->getFile($thumb);
                else
                    return '';
            }

            public function getUrl($thumb = false)
            {
                if ($this->getType() instanceof ImageType)
                    return PictureUtils::getImageUrlByObject($this) . $this->getFile($thumb);
                else
                    return '';
            }

            public function checkPermissions($label)
            {
                $perm = false;

                if ($this->getOwner() instanceof ImageOwner) {
                    $perm = $this->getOwner()->checkPermissions(AclAction::EDIT_ACTION);
                } else {
                    $perm = SecurityManager::isAuth() && $this->getUser()->getId() == SecurityManager::getUser()->getId();                            
                }

                return $perm;
            }
	}
?>