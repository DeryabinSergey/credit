<?php

class CategoryList extends baseFront implements UserController, SecurityController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->
                setTitle('Категории - Администрирование')->
                appendJavaScript('/i/category-list.js');
            
            $list = Criteria::create(Category::dao())->addOrder(OrderBy::create('sort')->asc())->getList();
            
            $model->set('list', $list);
        }
        
        return $model;
    }
    
    public function checkPermissions()
    {
        return SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::CATEGORY_ID);
    }
}