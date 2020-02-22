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
            
            if ($hasCompany) {
            
                $meetingList = array();

                $incomeList = 
                    Criteria::create(CreditRequestCreditor::dao())->
                        add(Expression::in('creditor', SecurityManager::getUser()->getCreditors(true)->getList()))->
                        add(Expression::eq('status', CreditRequestCreditorStatus::TYPE_INCOME))->
                        addOrder(OrderBy::create('id')->desc())->
                        getList();
                
                $inWorkCount = 
                    Criteria::create(CreditRequestCreditor::dao())->
                        addProjection(Projection::count('id', 'sum'))->
                        add(Expression::in('creditor', SecurityManager::getUser()->getCreditors(true)->getList()))->
                        add(Expression::eq('status', CreditRequestCreditorStatus::TYPE_CONCIDERED))->
                        getCustom('sum');
                
                $meetingIds = 
                    ArrayUtils::convertToPlainList(
                        Criteria::create(CreditRequestCreditorOffer::dao())->
                            setDistinct()->
                            addProjection(Projection::property('request'))->
                            add(Expression::eq('status', CreditRequestCreditorOfferStatus::TYPE_MEETING))->
                            getCustomList(),
                    'request_id');
                
                if ($meetingIds) {
                    $meetingList = 
                        Criteria::create(CreditRequestCreditor::dao())->
                            add(Expression::in('creditor', SecurityManager::getUser()->getCreditors(true)->getList()))->
                            add(Expression::eq('status', CreditRequestCreditorStatus::TYPE_CONCIDERED))->
                            add(Expression::in('id', $meetingIds))->
                            getList();
                }
            
                $model->
                    set('incomeList', $incomeList)->
                    set('inWorkCount', $inWorkCount)->
                    set('meetingList', $meetingList);
                
            }
            
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