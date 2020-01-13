<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-14 01:05:23                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoCreditRequest extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'CreditRequest', 8, true, true, false, null, null),
				'name' => LightMetaProperty::fill(new LightMetaProperty(), 'name', null, 'string', null, 255, false, true, false, null, null),
				'createdTime' => LightMetaProperty::fill(new LightMetaProperty(), 'createdTime', 'created_time', 'timestamp', 'Timestamp', null, true, true, false, null, null),
				'user' => LightMetaProperty::fill(new LightMetaProperty(), 'user', 'user_id', 'identifier', 'User', null, true, false, false, 1, 3),
				'type' => LightMetaProperty::fill(new LightMetaProperty(), 'type', 'type_id', 'enumeration', 'SubjectType', null, true, false, false, 1, 3),
				'category' => LightMetaProperty::fill(new LightMetaProperty(), 'category', 'category_id', 'identifier', 'Category', null, true, false, false, 1, 3),
				'summ' => LightMetaProperty::fill(new LightMetaProperty(), 'summ', null, 'integer', null, 8, true, true, false, null, null),
				'birthDate' => LightMetaProperty::fill(new LightMetaProperty(), 'birthDate', 'birth_date', 'timestamp', 'Timestamp', null, false, true, false, null, null),
				'profit' => LightMetaProperty::fill(new LightMetaProperty(), 'profit', null, 'integer', null, 8, true, true, false, null, null),
				'text' => LightMetaProperty::fill(new LightMetaProperty(), 'text', null, 'string', null, null, true, true, false, null, null),
				'passport' => LightMetaProperty::fill(new LightMetaProperty(), 'passport', null, 'integer', null, 8, false, true, false, null, null),
				'ogrn' => LightMetaProperty::fill(new LightMetaProperty(), 'ogrn', null, 'integer', null, 8, false, true, false, null, null)
			);
		}
	}
?>