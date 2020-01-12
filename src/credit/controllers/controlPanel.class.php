<?php

class controlPanel extends baseFront implements UserController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Личный Кабинет');
            
            $hasCompany = 
                Criteria::create(Creditor::dao())->
                    addProjection(Projection::count('id', 'sum'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    getCustom('sum');
            
            $list = 
                Criteria::create(Creditor::dao())->
                    add(Expression::isFalse('deleted'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    addOrder(OrderBy::create('id')->desc())->
                    getList(); 
            
            $model->
                set('list', $list)->
                set('hasCompany', $hasCompany);
        }
        return $model;
    }
}