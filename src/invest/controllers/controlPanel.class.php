<?php

class controlPanel extends baseFront implements UserController
{
    public function getModel(\HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        if ($this->isDisplayView()) {
            
            Singleton::getInstance('HTMLMetaManager')->setTitle('Личный Кабинет');
            
            $hasInvest = 
                Criteria::create(InvestorOffer::dao())->
                    addProjection(Projection::count('id', 'sum'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    getCustom('sum');
            
            $listIncomeRequests = $listIds = $requestIds = array();
            $inWorkCount = 0;
            
            $list = 
                Criteria::create(InvestorOffer::dao())->
                    add(Expression::isFalse('deleted'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    addOrder(OrderBy::create('id')->desc())->
                    getList(); 
            foreach($list as $item) {
                /**
                 * Выше был получен полный список и в нем есть неподтвержденные еще предложения инвестирования
                 */
                if (!$item->isActive()) continue;
                $listIds[] = $item->getId();
            }
                
            if ($listIds) {
                
                /**
                 * Входящие запросы инвестирования
                 * 
                 * Сначала получаем ID запросов, которым уже добавлены предложения с имеющимися у польлзователя кредитными предложениями
                 */
                $ids = 
                    ArrayUtils::convertToPlainList(
                        Criteria::create(CreditRequestCreditorInvestorOffer::dao())->
                            setDistinct(true)->
                            addProjection(Projection::property('request'))->
                            add(Expression::eq('request.status', CreditRequestCreditorStatus::TYPE_CONCIDERED))->
                            add(Expression::notNull('investSumm'))->
                            add(Expression::in('offer', $listIds)),
                        'request_id'
                    );
                $inWorkCount = count($ids);
                    
                /**
                 * Получаем для каждого кредитного предложения подходящие запросы, исключая
                 * полученные выше запросы с предложениями от инвестора
                 */
                foreach($list as $item) {
                    /**
                     * Выше был получен полный список и в нем есть неподтвержденные еще предложения инвестирования
                     */
                    if (!$item->isActive()) continue;

                    $rIdsCriteria = 
                        Criteria::create(CreditRequestCreditor::dao())->
                            addProjection(Projection::property('id'))->
                            add(Expression::eq('status', CreditRequestCreditorStatus::TYPE_CONCIDERED))->
                            add(Expression::notNull('investSumm'))->
                            add(Expression::gtEq('investPercents', $item->getPercents()));

                    if ($item->getMinPeriod()) {
                        $rIdsCriteria->add(Expression::gtEq('investPeriod', $item->getMinPeriod()));
                    } elseif($item->getMaxPeriod()) {
                        $rIdsCriteria->add(Expression::ltEq('investPeriod', $item->getMaxPeriod()));
                    }
                    if ($item->getMinSumm()) {
                        $rIdsCriteria->add(Expression::gtEq('investSumm', $item->getMinSumm()));
                    }
                    if ($ids) {
                        $rIdsCriteria->add(Expression::notIn('id', $ids));
                    }

                    $requestIds = array_unique(array_merge($requestIds, ArrayUtils::convertToPlainList($rIdsCriteria->getCustomList(), 'id')));
                }
                
                if ($requestIds) {
                    $listIncomeRequests = CreditRequestCreditor::dao()->getListByIds($requestIds);
                }
            }
            
            $model->
                set('list', $list)->
                set('listIncomeRequests', $listIncomeRequests)->
                set('inWorkCount', $inWorkCount)->
                set('hasInvest', $hasInvest);
        }
        
        return $model;
    }
}