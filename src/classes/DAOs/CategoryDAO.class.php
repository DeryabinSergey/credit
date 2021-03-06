<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2020-01-07 05:12:08                    *
 *   This file will never be generated again - feel free to edit.            *
 *****************************************************************************/

    class CategoryDAO extends AutoCategoryDAO
    {
        const QU_NAME   = 'uq_category_name';
		
        public function add(Identifiable $object)
        {
            return parent::add($object->setSort($this->getMaxSort() + 1));
        }
            
        protected function getMaxSort()
        {
            $max = $this->getCustom(Criteria::create($this)->addProjection(Projection::max('sort', 'max'))->toSelectQuery(), Cache::EXPIRES_MINIMUM);

            return $max['max'];
        }
    }
?>