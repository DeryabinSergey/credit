<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2019-12-26 13:54:24                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	class UserDAO extends AutoUserDAO
	{
            const UQ_PHONE = 'uq_user_phone';
            const UQ_EMAIL = 'uq_user_email';

            public function add(Identifiable $user)
            {
                if (!$user->getCreatedTime() instanceof Timestamp) {
                    $user->setCreatedTime(Timestamp::makeNow());
                }
                
                if (!$user->getSecurityType() instanceof SecurityType) {
                    $user->setSecurityType(SecurityType::create(SecurityType::TYPE_NORMAL));
                }

                return parent::add($user);
            }

            public function dropById($id)
            {
                try {
                    $tr = InnerTransaction::begin($this);

                    $user = $this->getById($id);
                    
                    $this->
                        dropUserActionLog($user)->
                        dropUserConfirm($user)->
                        dropUserSecurityLog($user)->
                        dropUserClones($user)->
                        dropUserAuthLog($user);
                    
                    $previewFiles = PictureUtils::getPreviewFiles($user);

                    if ($count = parent::dropById($id)) {
                        foreach($previewFiles as $preview)
                            if ($preview && is_file($preview) && file_exists($preview)) unlink($preview);
                    }

                    $tr->commit();
                } catch (Exception $e) {
                    $tr->rollback();

                    throw $e;
                }

                return $count;
            }

            public function dropByIds(array $ids)
            {
                try {
                    $tr = InnerTransaction::begin($this);
                    
                    $previewFiles = array();

                    foreach($ids as $id) {
                        $user = $this->getById($id);

                        $this->
                            dropUserActionLog($user)->
                            dropUserConfirm($user)->
                            dropUserSecurityLog($user)->
                            dropUserClones($user)->
                            dropUserAuthLog($user);
                        
                        $previewFiles = array_merge($previewFiles, PictureUtils::getPreviewFiles($user));
                    }                

                    if ($count = parent::dropByIds($ids)) {
                        foreach($previewFiles as $preview)
                            if ($preview && is_file($preview) && file_exists($preview)) unlink($preview);
                    }

                    $tr->commit();
                } catch (Exception $e) {
                    $tr->rollback();

                    throw $e;
                }

                return $count;
            }
            
            /**
             * Удалить историю изменений объекта пользователя
             * @param User $user
             * @return UserDAO
             */
            protected function dropUserActionLog(User $user)
            {
                $ids = array();
                $criteria = Criteria::create(ActionLog::dao())->
                    addProjection(Projection::property('id'))->
                    add(Expression::eq('objectName', get_class($user)))->
                    add(Expression::eq('objectId', $user->getId()));
                try {
                    $ids = $criteria->getCustomList();
                } catch(ObjectNotFoundException $e) { }
                if ($ids) {
                    ActionLog::dao()->dropByIds(ArrayUtils::convertToPlainList($ids, 'id'));
                }
                
                return $this;
            }
            
            /**
             * Удалить подтверждения пользователя
             * @param User $user
             * @return UserDAO
             */
            protected function dropUserConfirm(User $user)
            {
                $ids = array();
                $criteria = Criteria::create(Confirm::dao())->
                    addProjection(Projection::property('id'))->
                    add(Expression::eq('user', $user->getId()));
                try {
                    $ids = $criteria->getCustomList();
                } catch(ObjectNotFoundException $e) { }
                if ($ids) {
                    $criteria->getDao()->dropByIds(ArrayUtils::convertToPlainList($ids, 'id'));
                }         
                
                return $this;
            }
            
            /**
             * Удалить историю авторизаций пользователя
             * @param User $user
             * @return UserDAO
             */
            protected function dropUserSecurityLog(User $user)
            {
                $ids = array();
                $criteria = Criteria::create(SecurityLog::dao())->
                    addProjection(Projection::property('id'))->
                    add(Expression::eq('user', $user->getId()));
                try {
                    $ids = $criteria->getCustomList();
                } catch(ObjectNotFoundException $e) { }
                if ($ids) {
                    $criteria->getDao()->dropByIds(ArrayUtils::convertToPlainList($ids, 'id'));
                }         
                
                return $this;
            }
            
            /**
             * Удалить записи о клонах пользователя
             * @param User $user
             * @return UserDAO
             */
            protected function dropUserClones(User $user)
            {
                DBPool::getByDao($this)->queryNull(OSQL::delete()->from('user_clones')->where(Expression::orBlock(Expression::eq('user_id', $user->getId()), Expression::eq('clone_id', $user->getId()))));
                
                return $this;
            }
            
            /**
             * Удалить историю неудачных авторизаций пользователя
             * @param User $user
             * @return UserDAO
             */
            protected function dropUserAuthLog(User $user)
            {
                $ids = array();
                $criteria = Criteria::create(AuthLog::dao())->
                    addProjection(Projection::property('id'))->
                    add(Expression::eq('user', $user->getId()));
                try {
                    $ids = $criteria->getCustomList();
                } catch(ObjectNotFoundException $e) { }
                if ($ids) {
                    $criteria->getDao()->dropByIds(ArrayUtils::convertToPlainList($ids, 'id'));
                }         
                
                return $this;
            }
	}
?>