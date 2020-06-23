<?php

class newsList extends baseFront
{
    const ON_PAGE = 10;
    
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            $criteria = 
                Criteria::create(News::dao())->
                    addOrder(OrderBy::create('id')->desc())->
                    setLimit(self::ON_PAGE)->
                    setOffset(($this->form->getValueOrDefault('page') - 1) * self::ON_PAGE);
            
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
}