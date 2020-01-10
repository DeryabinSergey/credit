<?php

class AclActionList extends baseFront implements SecurityController, UserController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {

            Singleton::getInstance('HTMLMetaManager')->setTitle('Список действий прав - Администрирование');
            
            $list = Criteria::create(AclAction::dao())->addOrder(OrderBy::create('name')->asc())->getList();
            
            $model->set('list', $list);
        }
        
        return $model;
    }
    
    public function checkPermissions()
    {
        return SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::ACL_ID);
    }
}