<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-03-15 16:53:42                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoCategory extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'Category', 8, true, true, false, null, null),
				'name' => LightMetaProperty::fill(new LightMetaProperty(), 'name', null, 'string', null, 128, true, true, false, null, null),
				'sort' => LightMetaProperty::fill(new LightMetaProperty(), 'sort', null, 'integer', null, 4, true, true, false, null, null),
				'text' => LightMetaProperty::fill(new LightMetaProperty(), 'text', null, 'string', null, null, false, true, false, null, null),
				'pledge' => LightMetaProperty::fill(new LightMetaProperty(), 'pledge', null, 'boolean', null, null, true, true, false, null, null)
			);
		}
	}
?>