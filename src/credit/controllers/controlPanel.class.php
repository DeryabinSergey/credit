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
            
                $meetingList = $inWorkEmptyList = array();
                /**
                 * Здесь буду отмечать компании, которым пришел запрос - показывать ли в списке компанию кредитора если их больше одной
                 */
                $inWorkEmptyListCreditor = array();

                $incomeList = 
                    Criteria::create(CreditRequestCreditor::dao())->
                        add(Expression::in('creditor', SecurityManager::getUser()->getCreditors(true)->getList()))->
                        add(Expression::eq('status', CreditRequestCreditorStatus::TYPE_INCOME))->
                        addOrder(OrderBy::create('id')->desc())->
                        getList();
                
                $inWorkList = 
                    Criteria::create(CreditRequestCreditor::dao())->
                        add(Expression::in('creditor', SecurityManager::getUser()->getCreditors(true)->getList()))->
                        add(Expression::eq('status', CreditRequestCreditorStatus::TYPE_CONCIDERED))->
                        getList();
                
                foreach($inWorkList as $creditorRequest) {
                    if ($creditorRequest->getOffers()->getCount() == 0) {
                        if (!isset($inWorkEmptyListCreditor[$creditorRequest->getCreditorId()])) {
                            $inWorkEmptyListCreditor[$creditorRequest->getCreditorId()] = true;
                        }
                        $inWorkEmptyList[] = $creditorRequest;
                    }
                }
                
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
                    set('inWorkList', $inWorkList)->
                    set('inWorkEmptyLits', $inWorkEmptyList)->
                    set('inWornEmptyListShowCreditors', count($inWorkEmptyListCreditor) > 1)->
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