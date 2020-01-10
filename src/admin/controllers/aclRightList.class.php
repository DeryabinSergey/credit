<?php

class AclRightList extends baseFront implements SecurityController, UserController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Список прав - Администрирование');
            
            $list = Criteria::create(AclRight::dao())->addOrder(OrderBy::create('name')->asc())->getList();
            
            $model->set('list', $list);
        }
        
        return $model;
    }
    
    public function checkPermissions()
    {
        return SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::ACL_ID);
    }
}