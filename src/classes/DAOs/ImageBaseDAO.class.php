<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2019-12-26 13:54:24                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	abstract class ImageBaseDAO extends AutoImageBaseDAO
	{
            public function add(Identifiable $object)
            {
                if (!$object->getCreatedTime() instanceof Timestamp) {
                    $object->setCreatedTime(Timestamp::makeNow());
                }
                
                return 
                    parent::add(
                        $object->
                            setSort($this->getMaxSort($object->getOwner(), $object->getUser()) + 1)
                    );
            }

            public function dropById($id)
            {
                $image = $this->getById($id);

                $img = $image->getPath();
                $tmb = $image->getPath(true);

                try {
                    $tr = InnerTransaction::begin($this);

                    if ($count = parent::dropById($id)) {
                        if ($img && file_exists($img))
                            unlink($img);

                        if ($tmb && file_exists($tmb))
                            unlink($tmb);
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
                $filesList = array();
                foreach($ids as $id) {
                    $image = $this->getById($id);

                    if ($image->getPath())
                        $filesList[] = $image->getPath();
                    if ($image->getPath(true))
                        $filesList[] = $image->getPath(true);
                }

                try {
                    $tr = InnerTransaction::begin($this);

                    if ($count = parent::dropByIds($ids)) {
                        foreach($filesList as $file) {
                            if (file_exists($file))
                                unlink($file);
                        }
                    }

                    $tr->commit();
                } catch (Exception $e) {
                    $tr->rollback();

                    throw $e;
                }

                return $count;
            }

            protected function getMaxSort(ImageOwner $owner = null, User $user = null)
            {
                $criteria = Criteria::create($this)->
                    setProjection(Projection::max('sort', 'max'));
                
                if ($owner instanceof ImageOwner) {
                    $criteria->add(Expression::eq("owner", $owner->getId()));
                } else {
                    $criteria->
                        add(Expression::eq("user", $user->getId()))->
                        add(Expression::isNull('owner'));
                }
                
                return $criteria->getCustom('max');
            }
	}
?>