<?php

class CreditorList extends baseFront implements UserController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Мои кредитные организации - Панель управления');
            
            $list = 
                Criteria::create(Creditor::dao())->
                    add(Expression::isFalse('deleted'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    addOrder(OrderBy::create('id')->asc())->
                    getList();
            
            $model->set('list', $list);
        }
        
        return $model;
    }
}