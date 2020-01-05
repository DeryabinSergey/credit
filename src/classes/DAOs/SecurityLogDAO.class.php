<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 22:05:24                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class SecurityLogDAO extends AutoSecurityLogDAO
	{
            public function add(Identifiable $log)
            {
                return parent::add($log->setCreatedTime(Timestamp::makeNow()));
            }

            public function getLastUserLogs(User $user, $limit)
            {
                return $this->getListByQuery(
                    Criteria::create($this)->
                        add(Expression::eq('user', $user->getId()))->
                        addOrder(OrderBy::create('id')->desc())->
                        setLimit($limit)->
                        toSelectQuery(),
                    Cache::EXPIRES_MEDIUM);
            }
	}
?>