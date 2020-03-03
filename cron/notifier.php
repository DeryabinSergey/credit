<?php

require realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'../config.inc.php');

set_time_limit(0);

class CronNotifier extends BaseCron
{
    public static function create() { return new self; }

    public function process()
    {
        $this->
            creditRequestNotifier()->
            investRequestNotifier();

        return $this;
    }
    
    protected function creditRequestNotifier()
    {
        $list = 
            Criteria::create(CreditRequest::dao())->
                add(Expression::isFalse('deleted'))->
                add(Expression::eq('status', CreditRequestStatus::TYPE_CONCIDERED))->
                getList();
        
        foreach($list as $creditRequest) {
            $now = Timestamp::makeNow();
            
            $onlyDeclinded = true;
            $newOffers = 0;
            $hasAcceptedOffers = false;
            
            foreach($creditRequest->getCreditorRequests()->getList() as $creditorRequest) {
                $onlyDeclinded = $onlyDeclinded && $creditorRequest->getStatus()->getId() == CreditRequestCreditorStatus::TYPE_REJECT;
                if (!$onlyDeclinded) { break; }
            }
            
            foreach(
                $creditRequest->getCreditorOffers(array(), array(CreditRequestCreditorOfferStatus::TYPE_CANCELED, CreditRequestCreditorOfferStatus::TYPE_REJECT))
                as $offer
            ) {
                $hasAcceptedOffers = $hasAcceptedOffers || $offer->getStatus()->getId() != CreditRequestCreditorOfferStatus::TYPE_INCOME;
                if ($hasAcceptedOffers) { break; }
                if (
                    $offer->getStatus()->getId() == CreditRequestCreditorOfferStatus::TYPE_INCOME &&
                    (
                        !$creditRequest->getNotifiedTime() instanceof Timestamp ||
                        Timestamp::compare($creditRequest->getNotifiedTime(), $offer->getCreatedTime()) < 0
                    )
                ) {
                    $newOffers++;
                }
            }
            
            if ($onlyDeclinded) {
                $creditRequest->dao()->save($creditRequest->setStatusId(CreditRequestStatus::TYPE_REJECT));
                SmsUtils::send("7{$creditRequest->getUser()->getPhone()}", "По заявке от ".$creditRequest->getCreatedTime()->getDay()." ".RussianTextUtils::getMonthInGenitiveCase($creditRequest->getCreatedTime()->getMonth())." на ".number_format($creditRequest->getSumm(), 0, '.', ' ')."руб. пришел отказ от всех партнеров");
            } elseif (!$hasAcceptedOffers && $newOffers) {
                $creditRequest->dao()->save($creditRequest->setNotifiedTime($now));
                SmsUtils::send("7{$creditRequest->getUser()->getPhone()}", "По заявке от ".$creditRequest->getCreatedTime()->getDay()." ".RussianTextUtils::getMonthInGenitiveCase($creditRequest->getCreatedTime()->getMonth())." на ".number_format($creditRequest->getSumm(), 0, '.', ' ')." руб. поступило {$newOffers} ".RussianTextUtils::selectCaseForNumber($newOffers, array('кредитное предложение', 'кредитных предложения', 'кредитных предложений')).": ".CommonUtils::makeUrl('creditRequestEditor', array('action' => CommandContainer::ACTION_VIEW, 'id' => $creditRequest->getId()), PATH_WEB_BASE));
            }
        }
        
        return $this;
    }
    
    protected function investRequestNotifier()
    {
        $list = 
            Criteria::create(CreditRequestCreditor::dao())->
                add(Expression::isFalse('request.deleted'))->
                add(Expression::eq('status', CreditRequestCreditorStatus::TYPE_CONCIDERED))->
                add(Expression::isNull('investNotified'))->
                add(Expression::notNull('investSumm'))->
                getList();
        
        $investors = array();        
        foreach($list as $request) {
            $listInvestors = 
                ArrayUtils::convertToPlainList(
                    Criteria::create(InvestorOffer::dao())->
                        setDistinct(true)->
                        addProjection(Projection::property('user'))->
                        add(Expression::isFalse('user.ban'))->
                        add(Expression::isTrue('active'))->
                        add(Expression::isFalse('deleted'))->
                        add(Expression::gtEq('percents', $request->getInvestPercents()))->
                        add(
                            Expression::orBlock(
                                Expression::isNull('minPeriod'), 
                                Expression::gtEq('minPeriod', $request->getInvestPeriod())
                            )
                        )->
                        add(
                            Expression::orBlock(
                                Expression::isNull('maxPeriod'), 
                                Expression::ltEq('maxPeriod', $request->getInvestPeriod())
                            )
                        )->
                        add(
                            Expression::orBlock(
                                Expression::isNull('minSumm'), 
                                Expression::gtEq('minSumm', $request->getInvestSumm())
                            )
                        )->
                        getCustomList(),
                    'user_id');
            
            foreach($listInvestors as $investorId) {
                if (!isset($investors[$investorId])) { $investors[$investorId] = array(); }
                
                $investors[$investorId][] = $request;
            }
        }
        
        foreach($investors as $investorId => $listRequests) {
            $user = User::dao()->getById($investorId);
            $num = count($listRequests);
            
            if ($user->getEmail()) {
                Mail::create()->
                    setTo($user->getEmail())->
                    setFrom(DEFAULT_FROM)->
                    setSubject('Новые запросы инвестирования')->
                    setText(
                        "На портале ".
                        RussianTextUtils::selectCaseForNumber($num, array("добавлен {$num} запрос", "добавлены {$num} запроса", "добавлено {$num} запросов")).
                        " на инвестирование\r\n\r\nПосмотреть все новые запросы: ".CommonUtils::makeUrl('controlPanel', array(), PATH_WEB_INVESTOR)
                    )->
                    send();                
            }
        }
        
        foreach($list as $item) {
            $item->dao()->save($item->setInvestNotified(Timestamp::makeNow()));
        }
        
        return $this;
    }
}

$start = Timestamp::makeNow();
$report = "";

ob_start();
try {
    CronNotifier::create()->process();
} catch (Exception $e) {
    echo "Exception:\n";
    print_r($e);
}
$report = ob_get_clean();

if ($report) {
    echo "Начало: " . $start->toDateTime()."\n\n";
    echo $report;
    echo "\n\nОкончание: ". Timestamp::makeNow()->toDateTime() . "\n";
}