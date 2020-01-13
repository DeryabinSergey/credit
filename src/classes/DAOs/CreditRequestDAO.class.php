<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-12 23:19:36                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class CreditRequestDAO extends AutoCreditRequestDAO
	{
            /**
             * @param Identifiable $user
             * @return CreditRequest
             */
            public function add(Identifiable $creditor)
            {
                if (!$creditor->getCreatedTime() instanceof Timestamp) {
                    $creditor->setCreatedTime(Timestamp::makeNow());
                }

                return parent::add($creditor);
            }
	}
?>