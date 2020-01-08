<?php

class AdminCreditorList extends baseFront implements UserController, SecurityController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Кредиторы - Администрирование');
            
            $list = Criteria::create(Creditor::dao())->addOrder(OrderBy::create('id')->desc())->getList();
            
            $model->set('list', $list);
        }
        
        return $model;
    }
    
    public function checkPermissions()
    {
        return SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::CREDITOR_ID);
    }
}