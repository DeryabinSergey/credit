<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-06-23 13:22:32                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoNewsDAO extends StorableDAO
	{
		public function getTable()
		{
			return 'news';
		}
		
		public function getObjectName()
		{
			return 'News';
		}
		
		public function getSequence()
		{
			return 'news_id';
		}
	}
?>