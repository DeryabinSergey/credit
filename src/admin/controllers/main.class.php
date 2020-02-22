<?php

class main extends baseFront implements SecurityController, UserController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            
            if (SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::CREDIT_REQUEST_ID)) {
                $model->set('creditRequestNum', Criteria::create(CreditRequest::dao())->addProjection(Projection::count('id', 'num'))->add(Expression::isFalse('deleted'))->getCustom('num'));
            }
            if (SecurityManager::isAllowedAction(AclAction::PUBLISH_ACTION, AclContext::CREDIT_REQUEST_ID)) {
                $model->set('creditRequestNew', Criteria::create(CreditRequest::dao())->addProjection(Projection::count('id', 'num'))->add(Expression::isFalse('deleted'))->add(Expression::eq('status', CreditRequestStatus::TYPE_INCOME))->getCustom('num'));
                
                $list = array();
                $listIds = 
                    ArrayUtils::convertToPlainList(
                        Criteria::create(CreditRequestCreditorOffer::dao())->
                            addProjection(Projection::property('request'))->
                            add(Expression::eq('status', CreditRequestCreditorOfferStatus::TYPE_ACCEPTED))->
                            getCustomList(),
                    'request_id');
                
                if ($listIds) {
                    $list = 
                        Criteria::create(CreditRequest::dao())->
                            setDistinct()->
                            add(Expression::in('creditorRequests.id', $listIds))->
                            getList();
                }
                    
                $model->set('creditRequestAccepted', $list);
            }
            
            if (SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::INVESTOR_OFFER_ID)) {
                $model->set('investorOfferNum', Criteria::create(InvestorOffer::dao())->addProjection(Projection::count('id', 'num'))->add(Expression::isFalse('deleted'))->getCustom('num'));
            }
            if (SecurityManager::isAllowedAction(AclAction::PUBLISH_ACTION, AclContext::INVESTOR_OFFER_ID)) {
                $model->set('investorOfferModerate', Criteria::create(InvestorOffer::dao())->addProjection(Projection::count('id', 'num'))->add(Expression::isFalse('active'))->getCustom('num'));
            }
            
            if (SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::CREDITOR_ID)) {
                $model->set('creditorNum', Criteria::create(Creditor::dao())->addProjection(Projection::count('id', 'num'))->add(Expression::isFalse('deleted'))->getCustom('num'));
            }
            if (SecurityManager::isAllowedAction(AclAction::PUBLISH_ACTION, AclContext::CREDITOR_ID)) {
                $model->set('creditorModerate', Criteria::create(Creditor::dao())->addProjection(Projection::count('id', 'num'))->add(Expression::isFalse('active'))->getCustom('num'));
            }
            
        }
        
        return $model;
    }
    
    public function checkPermissions()
    {
        return SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::CONTROL_PANEL_ID);
    }
}