<?php

require realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'../config.inc.php');

set_time_limit(0);

class ExpiredRemover extends BaseCron
{
    public static function create() { return new self; }

    public function process()
    {
        $this->
            expiredConfirm()->
            expiredRegistration()->
            expiredBan()->
            expiredCreditRequestCreditor()->
            rejectCreditRequest();

        return $this;
    }
    
    protected function expiredConfirm()
    {
        $ids = 
            Criteria::create(Confirm::dao())->
                addProjection(Projection::property('id'))->
                add(Expression::ltEq('expiredTime', Timestamp::makeNow()->toDateTime()))->
                getCustomList();
        
        if ($ids) { 
            $ids = ArrayUtils::convertToPlainList($ids, 'id'); 
            
            Confirm::dao()->dropByIds($ids);
        }
        
        return $this;
    }
    
    protected function expiredRegistration()
    {
        $ids = 
            Criteria::create(User::dao())->
                addProjection(Projection::property('id'))->
                add(Expression::ltEq('createdTime', Timestamp::create("-7 day")->toDateTime()))->
                add(Expression::isFalse('active'))->
                getCustomList();
        
        if ($ids) { 
            $ids = ArrayUtils::convertToPlainList($ids, 'id'); 
            
            User::dao()->dropByIds($ids);
        }
        
        return $this;
    }
    
    protected function expiredBan()
    {
        $ids = 
            Criteria::create(User::dao())->
                addProjection(Projection::property('id'))->
                add(Expression::ltEq('banExpire', Timestamp::makeNow()->toDateTime()))->
                add(Expression::notNull('banExpire'))->
                add(Expression::isTrue('ban'))->
                getCustomList();
        
        if ($ids) { 
            $ids = ArrayUtils::convertToPlainList($ids, 'id'); 
            foreach($ids as $id) {
                $user = User::dao()->getById($id);
                
                $user->dao()->
                    save(
                        $user->
                            setBan(false)->
                            dropBanExpire()->
                            setBanComment(null)
                    );
            }
        }
        
        return $this;
    }
    
    protected function expiredCreditRequestCreditor()
    {
        $ids = 
            Criteria::create(CreditRequestCreditor::dao())->
                addProjection(Projection::property('id'))->
                add(Expression::ltEq('expired', Timestamp::makeNow()->toDateTime()))->
                add(Expression::in('status', array(CreditRequestCreditorStatus::TYPE_INCOME)))->
                getCustomList();
        
        if ($ids) { 
            CreditRequestCreditor::dao()->dropByIds(ArrayUtils::convertToPlainList($ids, 'id'));
        }
        
        return $this;
    }
    
    protected function rejectCreditRequest()
    {
        $list = 
            Criteria::create(CreditRequest::dao())->
                add(Expression::eq('status', CreditRequestStatus::TYPE_CONCIDERED))->
                getList();
        
        foreach($list as $creditRequest) {
            $onlyDeclinded = true;
            foreach($creditRequest->getCreditorRequests()->getList() as $creditorRequest) {
                $onlyDeclinded = $creditorRequest->getStatus()->getId() == CreditRequestCreditorStatus::TYPE_REJECT;
                if (!$onlyDeclinded) break;
            }
            
            if ($onlyDeclinded) {
                $creditRequest->dao()->save($creditRequest->setStatusId(CreditRequestStatus::TYPE_REJECT));
                SmsUtils::send("7{$creditRequest->getUser()->getPhone()}", "По заявке от ".$creditRequest->getCreatedTime()->getDay()." ".RussianTextUtils::getMonthInGenitiveCase($creditRequest->getCreatedTime()->getMonth())." на ".number_format($creditRequest->getSumm(), 0, '.', ' ')."руб. пришел отказ от всех партнеров");
            }
        }
        
        return $this;
    }
}

$start = Timestamp::makeNow();
$report = "";

ob_start();
try {
    ExpiredRemover::create()->process();
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