<?php

class creditorList extends baseFront implements UserController, SecurityController
{
    const ON_PAGE = 25;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->                
            addFilter(Primitive::choice('sort')->setList(array('id' => 'id'))->setDefault('id'))->
            addFilter(Primitive::choice('d')->setList(array('asc' => 'asc', 'desc' => 'desc'))->setDefault('desc'))->
            addFilter(Primitive::choice('delete')->setList(array(-1 => -1, 0 => 0, 1 => 1))->setDefault(0))->
            addFilter(Primitive::choice('active')->setList(array(-1 => -1, 0 => 0, 1 => 1))->setDefault(0))->
            addFilter(Primitive::identifierlist('user')->of('User')->setIgnoreEmpty()->setIgnoreWrong())->
            addFilter(Primitive::enumerationList('type')->of('SubjectType'))->
            addFilter(Primitive::identifierlist('category')->of('Category')->setIgnoreEmpty()->setIgnoreWrong());
    }
    
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Кредитные организации - Администрирование');
            
            $criteria = 
                Criteria::create(Creditor::dao())->
                    setDistinct()->
                    addOrder(OrderBy::create($this->form->getValueOrDefault('sort'))->{$this->form->getValueOrDefault('d')}())->
                    setLimit(self::ON_PAGE)->
                    setOffset(($this->form->getValueOrDefault('page') - 1) * self::ON_PAGE);
            
            if ($this->form->getValueOrDefault('active') == -1) {
                $criteria->add(Expression::isFalse('active'));
            } elseif ($this->form->getValueOrDefault('active') == 1) {
                $criteria->add(Expression::isTrue('active'));
            } 
            if ($this->form->getValueOrDefault('delete') == -1) {
                $criteria->add(Expression::isFalse('deleted'));
            } elseif ($this->form->getValueOrDefault('delete') == 1) {
                $criteria->add(Expression::isTrue('deleted'));
            }
            if ($this->form->getValue('user')) {
                $criteria->add(Expression::in('user', $this->form->exportValue('user')));
            }
            if ($this->form->getValue('category')) {
                $criteria->add(Expression::in('categories.category', $this->form->exportValue('category')));
            }
            if ($this->form->getValue('type')) {
                $criteria->add(Expression::in('type', $this->form->exportValue('type')));
            }
            
            $queryResult = $this->getQueryResult($criteria->toSelectQuery(), $criteria->getDao());

            if ($queryResult->getCount() > 0 && $queryResult->getCount() <= ($this->form->getValueOrDefault('page') - 1) * self::ON_PAGE) {
                $this->view = RedirectView::create(CommonUtils::makeUrl(get_class($this), array_merge($this->getFilter(), array('page' => $queryResult->getCount() > 0 ? ceil($queryResult->getCount() / self::ON_PAGE) : 1))));
                return $model->clean();
            }
            
            $model->
                set('categories', Criteria::create(Category::dao())->addOrder(OrderBy::create('sort')->asc())->getList())->
            	set('result', $queryResult)->
            	set('onPage', self::ON_PAGE);             
        }
        
        return $model;
    }
    
    public function checkPermissions()
    {
        return SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::CREDITOR_ID);
    }
}