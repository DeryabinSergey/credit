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
            expiredBan();

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