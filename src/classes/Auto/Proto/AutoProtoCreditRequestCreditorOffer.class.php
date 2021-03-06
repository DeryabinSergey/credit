<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-02-20 01:47:49                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	abstract class AutoProtoCreditRequestCreditorOffer extends AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array(
				'id' => LightMetaProperty::fill(new LightMetaProperty(), 'id', null, 'integerIdentifier', 'CreditRequestCreditorOffer', 8, true, true, false, null, null),
				'request' => LightMetaProperty::fill(new LightMetaProperty(), 'request', 'request_id', 'identifier', 'CreditRequestCreditor', null, true, false, false, 1, 3),
				'status' => LightMetaProperty::fill(new LightMetaProperty(), 'status', 'status_id', 'enumeration', 'CreditRequestCreditorOfferStatus', null, true, false, false, 1, 3),
				'createdTime' => LightMetaProperty::fill(new LightMetaProperty(), 'createdTime', 'created_time', 'timestamp', 'Timestamp', null, true, true, false, null, null),
				'summ' => LightMetaProperty::fill(new LightMetaProperty(), 'summ', null, 'integer', null, 4, true, true, false, null, null),
				'minPeriod' => LightMetaProperty::fill(new LightMetaProperty(), 'minPeriod', 'min_period', 'integer', null, 4, false, true, false, null, null),
				'maxPeriod' => LightMetaProperty::fill(new LightMetaProperty(), 'maxPeriod', 'max_period', 'integer', null, 4, false, true, false, null, null),
				'percents' => LightMetaProperty::fill(new LightMetaProperty(), 'percents', null, 'float', null, 4, true, true, false, null, null),
				'percentsOnly' => LightMetaProperty::fill(new LightMetaProperty(), 'percentsOnly', 'percents_only', 'boolean', null, null, true, true, false, null, null),
				'address' => LightMetaProperty::fill(new LightMetaProperty(), 'address', null, 'string', null, null, false, true, false, null, null),
				'date' => LightMetaProperty::fill(new LightMetaProperty(), 'date', null, 'date', 'Date', null, false, true, false, null, null),
				'time' => LightMetaProperty::fill(new LightMetaProperty(), 'time', null, 'time', 'Time', null, false, true, false, null, null),
				'text' => LightMetaProperty::fill(new LightMetaProperty(), 'text', null, 'string', null, null, false, true, false, null, null)
			);
		}
	}
?>