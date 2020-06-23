<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-06-23 13:22:32                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class NewsDAO extends AutoNewsDAO
	{
            /**
             * @param Identifiable $news
             * @return News
             */
            public function add(Identifiable $news)
            {
                if (!$news->getCreatedDate() instanceof Date) {
                    $news->setCreatedDate(Date::makeToday());
                }
		
		if (!$news->getType() instanceof NewsType) {
		    $news->setType(NewsType::create(NewsType::TYPE_MAIN));
		}
		
		if (!$news->getSid()) {
		    $news->setSid(
			preg_replace("/[^\w\-]/isu", "",
			    preg_replace("/\s/isu", "-", mb_strtolower(ViewTextUtils::transliterate($news->getTitle())))
			)
		    );
		}

                return parent::add($news);
            }
	}
?>