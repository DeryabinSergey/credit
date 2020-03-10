<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-08 18:33:13                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

	final class CreditorDAO extends AutoCreditorDAO
	{
            /**
             * @param Identifiable $user
             * @return Creditor
             */
            public function add(Identifiable $creditor)
            {
                if (!$creditor->getCreatedTime() instanceof Timestamp) {
                    $creditor->setCreatedTime(Timestamp::makeNow());
                }

                return parent::add($creditor);
            }

            public function dropById($id)
            {
                try {
                    $tr = InnerTransaction::begin($this);
                    /**
                     * @var Creditor
                     */
                    $creditor = $this->getById($id);
                    
                    $creditor->getCategories()->dropList();
                    
                    $previewFiles = PictureUtils::getPreviewFiles($creditor);

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
                        $creditor = $this->getById($id);

                        $creditor->getCategories()->dropList();
                        
                        $previewFiles = array_merge($previewFiles, PictureUtils::getPreviewFiles($creditor));
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
             * @param Creditor $creditor
             * @return Creditor
             */
            public function markAsDeleted(Creditor $creditor)
            {
                return $this->save($creditor->setDeleted(true));
            }
            
            /**
             * @param Creditor $creditor
             * @return $creditor
             */
            public function restore(Creditor $creditor)
            {
                return $this->save($creditor->setDeleted(false));
            }
	}
?>