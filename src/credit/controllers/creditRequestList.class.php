<?php

class creditRequestList extends baseFront implements UserController
{
    const ON_PAGE = 25;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->
            addFilter(Primitive::choice('sort')->setList(array('id' => 'id'))->setDefault('id'))->
            addFilter(Primitive::choice('d')->setList(array('asc' => 'asc', 'desc' => 'desc'))->setDefault('desc'))->
            addFilter(Primitive::enumerationList('type')->of('SubjectType'))->
            addFilter(Primitive::identifierlist('creditor')->of('Creditor')->setIgnoreEmpty()->setIgnoreWrong())->
            addFilter(Primitive::identifierlist('category')->of('Category')->setIgnoreEmpty()->setIgnoreWrong())->
            addFilter(Primitive::enumerationList('status')->of('CreditRequestCreditorStatus'));
    }
    
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Запросы кредита - Личный кабинет');
            
            $criteria = 
                Criteria::create(CreditRequestCreditor::dao())->
                    add(Expression::in('creditor', SecurityManager::getUser()->getCreditors(true)->getList()))->
                    addOrder(OrderBy::create($this->form->getValueOrDefault('sort'))->{$this->form->getValueOrDefault('d')}())->
                    setLimit(self::ON_PAGE)->
                    setOffset(($this->form->getValueOrDefault('page') - 1) * self::ON_PAGE);
            
            if ($this->form->getValue('status')) {
                $criteria->add(Expression::in('status', $this->form->exportValue('status')));
            }
            if ($this->form->getValue('creditor')) {
                $criteria->add(Expression::in('creditor', $this->form->exportValue('creditor')));
            }
            if ($this->form->getValue('category')) {
                $criteria->add(Expression::in('request.category', $this->form->exportValue('category')));
            }
            if ($this->form->getValue('type')) {
                $criteria->add(Expression::in('request.type', $this->form->exportValue('type')));
            }
            
            $queryResult = $this->getQueryResult($criteria->toSelectQuery(), $criteria->getDao());

            if ($queryResult->getCount() > 0 && $queryResult->getCount() <= ($this->form->getValueOrDefault('page') - 1) * self::ON_PAGE) {
                $this->view = RedirectView::create(CommonUtils::makeUrl(get_class($this), array_merge($this->getFilter(), array('page' => $queryResult->getCount() > 0 ? ceil($queryResult->getCount() / self::ON_PAGE) : 1))));
                return $model->clean();
            }
            
            $companies = $categories = array();
            /**
             * Получаем список компаний пользователя, для которых в принципе есть любые запросы на кредит
             */
            if (SecurityManager::getUser()->getCreditors(true)->getList()) {
                $listCompanyIds = 
                    ArrayUtils::convertToPlainList(
                        Criteria::create(CreditRequestCreditor::dao())->
                            setDistinct()->
                            addProjection(Projection::property('creditor'))->
                            add(Expression::in('creditor', SecurityManager::getUser()->getCreditors(true)->getList()))->
                            getCustomList(),
                        'creditor_id'
                    );
                
                if ($listCompanyIds) {
                    $companies = Creditor::dao()->getListByIds($listCompanyIds);
             
                    /**
                     * Получаем категории запросов, которые в принципе были к компаниям пользователя
                     */
                    $categoriesIds = 
                        ArrayUtils::convertToPlainList(
                            Criteria::create(CreditRequestCreditor::dao())->
                                addProjection(Projection::property('request.category'))->
                                add(Expression::in('creditor', $listCompanyIds))->
                                getCustomList(),
                            'category_id'
                        );
                    
                    if ($categoriesIds) {
                        $categories = Criteria::create(Category::dao())->addOrder(OrderBy::create('sort')->asc())->add(Expression::in('id', $categoriesIds))->getList();
                    }
                }
            }
            
            $model->
                set('categories', $categories)->
                set('creditors', $companies)->
            	set('result', $queryResult)->
            	set('onPage', self::ON_PAGE); 
        }
        
        return $model;
    }
}