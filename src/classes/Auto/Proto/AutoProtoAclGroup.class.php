<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-05 14:55:03                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoAclGroup extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'AclGroup', 8, true, true, false, null, null),
				'name' => LightMetaProperty::fill(new LightMetaProperty(), 'name', null, 'string', null, 64, true, true, false, null, null),
				'rights' => LightMetaProperty::fill(new LightMetaProperty(), 'rights', 'rights_id', 'identifierList', 'AclGroupRight', null, true, false, false, 2, null)
			);
		}
	}
?>