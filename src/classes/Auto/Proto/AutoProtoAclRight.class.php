<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-05 14:55:03                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoAclRight extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'AclRight', 8, true, true, false, null, null),
				'name' => LightMetaProperty::fill(new LightMetaProperty(), 'name', null, 'string', null, 64, true, true, false, null, null),
				'action' => LightMetaProperty::fill(new LightMetaProperty(), 'action', 'action_id', 'integerIdentifier', 'AclAction', null, true, false, false, 1, 3),
				'context' => LightMetaProperty::fill(new LightMetaProperty(), 'context', 'context_id', 'integerIdentifier', 'AclContext', null, true, false, false, 1, 3),
				'sectionId' => LightMetaProperty::fill(new LightMetaProperty(), 'sectionId', 'section_id', 'integer', null, 8, false, true, false, null, null),
				'sectionType' => LightMetaProperty::fill(new LightMetaProperty(), 'sectionType', 'section_type', 'string', null, null, false, true, false, null, null)
			);
		}
	}
?>