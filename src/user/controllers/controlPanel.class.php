<?php

class controlPanel extends baseFront implements UserController
{
    public function __construct()
    {
        parent::__construct();
        
        $this->addFilter(Primitive::boolean('all'));
    }
    
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
            
            $requestCriteria = 
                Criteria::create(CreditRequest::dao())->
                    add(Expression::isFalse('deleted'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    addOrder(OrderBy::create('id')->desc());
            if (!$this->form->getValue('all')) {
                $requestCriteria->add(Expression::in('status', array(CreditRequestStatus::TYPE_INCOME, CreditRequestStatus::TYPE_CONCIDERED)));
            }
            $list = $requestCriteria->getList();            
            
            $model->
                set('list', $list)->
                set('isAdded', $request->hasGetVar('added'))->
                set('hasCredit', $hasCredit);
        }
        
        return $model;
    }
}