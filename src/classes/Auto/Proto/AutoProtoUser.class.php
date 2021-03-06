<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-03-18 19:38:29                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoUser extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'User', 8, true, true, false, null, null),
				'name' => LightMetaProperty::fill(new LightMetaProperty(), 'name', null, 'string', null, 255, false, true, false, null, null),
				'createdTime' => LightMetaProperty::fill(new LightMetaProperty(), 'createdTime', 'created_time', 'timestamp', 'Timestamp', null, true, true, false, null, null),
				'email' => LightMetaProperty::fill(new LightMetaProperty(), 'email', null, 'string', null, 255, false, true, false, null, null),
				'password' => LightMetaProperty::fill(new LightMetaProperty(), 'password', null, 'string', null, 255, true, true, false, null, null),
				'phone' => LightMetaProperty::fill(new LightMetaProperty(), 'phone', null, 'integer', null, 8, true, true, false, null, null),
				'active' => LightMetaProperty::fill(new LightMetaProperty(), 'active', null, 'boolean', null, null, true, true, false, null, null),
				'ban' => LightMetaProperty::fill(new LightMetaProperty(), 'ban', null, 'boolean', null, null, true, true, false, null, null),
				'banExpire' => LightMetaProperty::fill(new LightMetaProperty(), 'banExpire', 'ban_expire', 'timestamp', 'Timestamp', null, false, true, false, null, null),
				'banComment' => LightMetaProperty::fill(new LightMetaProperty(), 'banComment', 'ban_comment', 'string', null, 255, false, true, false, null, null),
				'lastLog' => LightMetaProperty::fill(new LightMetaProperty(), 'lastLog', 'last_log', 'timestamp', 'Timestamp', null, false, true, false, null, null),
				'lastVisit' => LightMetaProperty::fill(new LightMetaProperty(), 'lastVisit', 'last_visit', 'timestamp', 'Timestamp', null, false, true, false, null, null),
				'ip' => LightMetaProperty::fill(new LightMetaProperty(), 'ip', null, 'integer', null, 8, false, true, false, null, null),
				'sid' => LightMetaProperty::fill(new LightMetaProperty(), 'sid', null, 'string', null, 64, false, true, false, null, null),
				'securityType' => LightMetaProperty::fill(new LightMetaProperty(), 'securityType', 'security_type_id', 'enumeration', 'SecurityType', null, true, false, false, 1, 3),
				'group' => LightMetaProperty::fill(new LightMetaProperty(), 'group', 'group_id', 'integerIdentifier', 'AclGroup', null, false, false, false, 1, 3),
				'telegramId' => LightMetaProperty::fill(new LightMetaProperty(), 'telegramId', 'telegram_id', 'integer', null, 8, false, true, false, null, null),
				'telegramBotEnabled' => LightMetaProperty::fill(new LightMetaProperty(), 'telegramBotEnabled', 'telegram_bot_enabled', 'boolean', null, null, true, true, false, null, null),
				'clones' => LightMetaProperty::fill(new LightMetaProperty(), 'clones', 'user_id', 'identifierList', 'User', null, false, false, false, 3, null),
				'creditors' => LightMetaProperty::fill(new LightMetaProperty(), 'creditors', 'creditors_id', 'identifierList', 'Creditor', null, false, false, false, 2, null)
			);
		}
	}
?>