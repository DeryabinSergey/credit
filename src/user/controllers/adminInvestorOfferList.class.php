<?php

class AdminInvestorOfferList extends baseFront implements UserController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Предложения инвестирования - Администрирование');
            
            $list = Criteria::create(InvestorOffer::dao())->addOrder(OrderBy::create('id')->desc())->getList();
            
            $model->set('list', $list);
        }
        
        return $model;
    }
}