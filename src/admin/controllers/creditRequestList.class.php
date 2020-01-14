<?php

class creditRequestList extends baseFront implements UserController, SecurityController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Запросы кредита - Администрирование');
            
            $list = Criteria::create(CreditRequest::dao())->addOrder(OrderBy::create('id')->desc())->getList();
            
            $model->set('list', $list);
        }
        
        return $model;
    }
    
    public function checkPermissions()
    {
        return SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::CREDIT_REQUEST_ID);
    }
}