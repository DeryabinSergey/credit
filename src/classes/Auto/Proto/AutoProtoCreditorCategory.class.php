<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-08 18:33:13                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoCreditorCategory extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'CreditorCategory', 8, true, true, false, null, null),
				'creditor' => LightMetaProperty::fill(new LightMetaProperty(), 'creditor', 'creditor_id', 'identifier', 'Creditor', null, true, false, false, 1, 3),
				'category' => LightMetaProperty::fill(new LightMetaProperty(), 'category', 'category_id', 'identifier', 'Category', null, true, false, false, 1, 3)
			);
		}
	}
?>