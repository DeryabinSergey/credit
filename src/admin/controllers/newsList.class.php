<?php

class newsList extends baseFront implements UserController, SecurityController
{
    const ON_PAGE = 25;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->
            addFilter(Primitive::choice('sort')->setList(array('id' => 'id'))->setDefault('id'))->
            addFilter(Primitive::choice('d')->setList(array('asc' => 'asc', 'desc' => 'desc'))->setDefault('desc'))->
            addFilter(Primitive::enumerationList('type')->of('NewsType'));
    }
    
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->
                setTitle('Новости - Администрирование');
            
            $criteria = 
                Criteria::create(News::dao())->
                    addOrder(OrderBy::create($this->form->getValueOrDefault('sort'))->{$this->form->getValueOrDefault('d')}())->
                    setLimit(self::ON_PAGE)->
                    setOffset(($this->form->getValueOrDefault('page') - 1) * self::ON_PAGE);
		    
            if ($this->form->getValue('type')) {
                $criteria->add(Expression::in('type', $this->form->exportValue('type')));
            }
            
            $queryResult = $this->getQueryResult($criteria->toSelectQuery(), $criteria->getDao());

            if ($queryResult->getCount() > 0 && $queryResult->getCount() <= ($this->form->getValueOrDefault('page') - 1) * self::ON_PAGE) {
                $this->view = RedirectView::create(CommonUtils::makeUrl(get_class($this), array_merge($this->getFilter(), array('page' => $queryResult->getCount() > 0 ? ceil($queryResult->getCount() / self::ON_PAGE) : 1))));
                return $model->clean();
            }
            
            $model->
            	set('result', $queryResult)->
            	set('onPage', self::ON_PAGE); 
        }
        
        return $model;
    }
    
    public function checkPermissions()
    {
        return SecurityManager::isAllowedAction(AclAction::VIEW_ACTION, AclContext::NEWS_ID);
    }
}