<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-02-12 01:13:54                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoCreditRequest extends ProtoImageOwner
	{
		protected function makePropertyList()
		{
			return
				array_merge(
					parent::makePropertyList(),
					array(
						'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'CreditRequest', 8, true, true, false, null, null),
						'createdTime' => LightMetaProperty::fill(new LightMetaProperty(), 'createdTime', 'created_time', 'timestamp', 'Timestamp', null, true, true, false, null, null),
						'status' => LightMetaProperty::fill(new LightMetaProperty(), 'status', 'status_id', 'enumeration', 'CreditRequestStatus', null, true, false, false, 1, 3),
						'user' => LightMetaProperty::fill(new LightMetaProperty(), 'user', 'user_id', 'identifier', 'User', null, true, false, false, 1, 3),
						'type' => LightMetaProperty::fill(new LightMetaProperty(), 'type', 'type_id', 'enumeration', 'SubjectType', null, true, false, false, 1, 3),
						'deleted' => LightMetaProperty::fill(new LightMetaProperty(), 'deleted', null, 'boolean', null, null, true, true, false, null, null),
						'category' => LightMetaProperty::fill(new LightMetaProperty(), 'category', 'category_id', 'identifier', 'Category', null, true, false, false, 1, 3),
						'name' => LightMetaProperty::fill(new LightMetaProperty(), 'name', null, 'string', null, 255, false, true, false, null, null),
						'summ' => LightMetaProperty::fill(new LightMetaProperty(), 'summ', null, 'integer', null, 8, true, true, false, null, null),
						'birthDate' => LightMetaProperty::fill(new LightMetaProperty(), 'birthDate', 'birth_date', 'timestamp', 'Timestamp', null, false, true, false, null, null),
						'profit' => LightMetaProperty::fill(new LightMetaProperty(), 'profit', null, 'integer', null, 8, false, true, false, null, null),
						'text' => LightMetaProperty::fill(new LightMetaProperty(), 'text', null, 'string', null, null, true, true, false, null, null),
						'passport' => LightMetaProperty::fill(new LightMetaProperty(), 'passport', null, 'integer', null, 8, false, true, false, null, null),
						'ogrn' => LightMetaProperty::fill(new LightMetaProperty(), 'ogrn', null, 'integer', null, 8, false, true, false, null, null),
						'images' => LightMetaProperty::fill(new LightMetaProperty(), 'images', 'images_id', 'identifierList', 'CreditRequestImage', null, false, false, false, 2, null),
						'creditorRequests' => LightMetaProperty::fill(new LightMetaProperty(), 'creditorRequests', 'creditor_requests_id', 'identifierList', 'CreditRequestCreditor', null, false, false, false, 2, null)
					)
				);
		}
	}
?>