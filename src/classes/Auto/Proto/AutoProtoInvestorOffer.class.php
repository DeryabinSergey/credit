<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-08 12:23:33                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoInvestorOffer extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'InvestorOffer', 8, true, true, false, null, null),
				'createdTime' => LightMetaProperty::fill(new LightMetaProperty(), 'createdTime', 'created_time', 'timestamp', 'Timestamp', null, true, true, false, null, null),
				'type' => LightMetaProperty::fill(new LightMetaProperty(), 'type', 'type_id', 'enumeration', 'SubjectType', null, true, false, false, 1, 3),
				'user' => LightMetaProperty::fill(new LightMetaProperty(), 'user', 'user_id', 'identifier', 'User', null, true, false, false, 1, 3),
				'active' => LightMetaProperty::fill(new LightMetaProperty(), 'active', null, 'boolean', null, null, true, true, false, null, null),
				'deleted' => LightMetaProperty::fill(new LightMetaProperty(), 'deleted', null, 'boolean', null, null, true, true, false, null, null),
				'minPeriod' => LightMetaProperty::fill(new LightMetaProperty(), 'minPeriod', 'min_period', 'integer', null, 4, false, true, false, null, null),
				'maxPeriod' => LightMetaProperty::fill(new LightMetaProperty(), 'maxPeriod', 'max_period', 'integer', null, 4, false, true, false, null, null),
				'minSumm' => LightMetaProperty::fill(new LightMetaProperty(), 'minSumm', 'min_summ', 'integer', null, 8, false, true, false, null, null),
				'maxSumm' => LightMetaProperty::fill(new LightMetaProperty(), 'maxSumm', 'max_summ', 'integer', null, 8, true, true, false, null, null),
				'percents' => LightMetaProperty::fill(new LightMetaProperty(), 'percents', null, 'float', null, 4, true, true, false, null, null)
			);
		}
	}
?>