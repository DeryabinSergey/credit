<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-02-08 14:59:33                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoCreditRequestImage extends ProtoImageBase
	{
		protected function makePropertyList()
		{
			return
				array_merge(
					parent::makePropertyList(),
					array(
						'owner' => LightMetaProperty::fill(new LightMetaProperty(), 'owner', 'owner_id', 'identifier', 'CreditRequest', null, false, false, false, 1, 3),
						'fileName' => LightMetaProperty::fill(new LightMetaProperty(), 'fileName', 'file_name', 'string', null, 128, true, true, false, null, null),
						'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'CreditRequestImage', 8, true, true, false, null, null)
					)
				);
		}
	}
?>