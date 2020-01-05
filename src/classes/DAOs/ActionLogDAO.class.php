<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-04 15:15:32                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	class ActionLogDAO extends AutoActionLogDAO
	{
            /**
             * @param Identifier $object
             * @return ActionLog
             */
            public function add(Identifiable $object)
            {
                return parent::add($object->setCreatedTime(Timestamp::makeNow()));
            }
            
            /**
             * Удалить историю действий с объектом по имени и идентификатору
             * @param Int $id - идентификатор
             * @param String $name - имя (Класс) объекта
             * @return Int - количество удаленных записей
             */
            public function dropByObject($id, $name)
            {
                $ids = array();
                $criteria = Criteria::create($this)->
                    addProjection(Projection::property('id'))->
                    add(Expression::eq('objectName', $name))->
                    add(Expression::eq('objectId', $id));
                
                try {
                    $ids = $criteria->getCustomList();
                } catch(ObjectNotFoundException $e) { }
                
                $result = 0;
                
                if ($ids) {
                    $result = $this->dropByIds(ArrayUtils::convertToPlainList($ids, 'id'));
                } 
                
                return $result;
            }
	}
?>