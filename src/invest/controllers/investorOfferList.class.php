<?php

class investorOfferList extends baseFront implements UserController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Мои Инвестиции - Кабинет');
            
            $list = 
                Criteria::create(InvestorOffer::dao())->
                    add(Expression::isFalse('deleted'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    addOrder(OrderBy::create('id')->desc())->
                    getList();
            
            $model->set('list', $list);
        }
        
        return $model;
    }
}