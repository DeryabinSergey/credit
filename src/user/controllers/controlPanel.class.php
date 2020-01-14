<?php

class controlPanel extends baseFront implements UserController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Личный Кабинет');
            
            $hasCredit = 
                Criteria::create(CreditRequest::dao())->
                    addProjection(Projection::count('id', 'sum'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    getCustom('sum');
            
            $list = 
                Criteria::create(CreditRequest::dao())->
                    add(Expression::isFalse('deleted'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    addOrder(OrderBy::create('id')->desc())->
                    getList();            
            
            $model->
                set('list', $list)->
                set('isAdded', $request->hasGetVar('added'))->
                set('hasCredit', $hasCredit);
        }
        
        return $model;
    }
}