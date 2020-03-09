<?php

require realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'../config.inc.php');

set_time_limit(0);

class CronNotifier extends BaseCron
{
    /**
     * Частота запуска данного скрипта
     */
    const INTERVAL      = 10;
    
    /**
     * Время запуска данного скрипта
     * @var Timestamp
     */
    private $start = null;
    
    public static function create() { return new self; }

    public function process()
    {
        $this->start = Timestamp::makeNow();
        
        $this->
            creditRequestNewImages()->
            creditRequestNotifier()->
            investRequestNotifier();

        return $this;
    }
    
    protected function creditRequestNewImages()
    {
        /** Получаем сначала все загруженные изображения, привязанные к объекту за время с момента предыдущего запуска **/
        $dt = $this->start->spawn("-".self::INTERVAL." minute");
        $creditRequestIds = 
            ArrayUtils::convertToPlainList(
                Criteria::create(CreditRequestImage::dao())->
                    addProjection(Projection::property('owner'))->
                    add(Expression::gtEq('createdTime', $dt->toString()))->
                    add(Expression::notNull('owner'))->
                    setDistinct(true)->
                    getCustomList(),
                'owner_id');
                
        $moderatorImagesList = array();
        $creditorImagesList = array();
        if ($creditRequestIds) {
            $creditRequestList = CreditRequest::dao()->getListByIds($creditRequestIds);
            
            foreach($creditRequestList as $creditRequest) {
                if ($creditRequest->isDeleted() || in_array($creditRequest->getStatus()->getId(), array(CreditRequestStatus::TYPE_REJECT, CreditRequestStatus::TYPE_CANCELED))) continue;

                if ($creditRequest->getCreditorRequests()->isFetched() && $creditRequest->getCreditorRequests()->getCriteria()) {
                    $creditRequest->getCreditorRequests()->setCriteria(Criteria::create())->clean();
                }     
                if ($creditRequest->getImages()->isFetched() && $creditRequest->getImages()->getCriteria()) {
                    $creditRequest->getImages()->setCriteria(Criteria::create())->clean();
                }                   
                $images = $creditRequest->getImages()->setCriteria(Criteria::create()->add(Expression::gtEq('createdTime', $dt->toString())))->getList();
                $creditorRequests = $creditRequest->getCreditorRequests()->setCriteria(Criteria::create()->add(Expression::in('status', array(CreditRequestCreditorStatus::TYPE_CONCIDERED))))->getList();
                
                /** Сначала смотрим надо ли уведомлять модератора **/
                foreach($images as $image) {
                    /** 
                     * Здесь можно было бы еще проверить, что фото загружено автором запроса, но с другой стороны мало ли.
                     * Оставим на тот случай, если модераторов будет много - пускай каждый получает уведомления что что-то загрузили
                     */
                    if (Timestamp::compare($image->getCreatedTime(), $creditRequest->getCreatedTime())) {
                        /** Изображение было загружено после создания запроса **/
                        if (!isset($moderatorImagesList[$creditRequest->getId()])) {
                            $moderatorImagesList[$creditRequest->getId()] = array('object' => $creditRequest, 'list' => array());
                        }
                        
                        $moderatorImagesList[$creditRequest->getId()]['list'][] = $image;
                    }
                    
                    foreach($creditorRequests as $creditorRequest) {
                        if (Timestamp::compare($image->getCreatedTime(), $creditorRequest->getCreatedTime())) {
                            /** Изображение было загружно после того как поступило к кредитной организации **/
                            if (!isset($creditorImagesList[$creditorRequest->getCreditor()->getUser()->getId()])) { $creditorImagesList[$creditorRequest->getCreditor()->getUser()->getId()] = array(); }
                            if (!isset($creditorImagesList[$creditorRequest->getCreditor()->getUser()->getId()][$creditorRequest->getId()])) { 
                                $creditorImagesList[$creditorRequest->getCreditor()->getUser()->getId()][$creditorRequest->getId()] = 
                                    array(
                                        'object' => $creditorRequest,
                                        'list' => array()
                                    ); 
                            }
                            
                            $creditorImagesList[$creditorRequest->getCreditor()->getUser()->getId()][$creditorRequest->getId()]['list'][] = $image;
                        }
                    }
                }
            }
        }
        
        /** Отправка уведомлений пользователям с правами на публикацию заявлений на кредит **/
        if ($moderatorImagesList) {
                    
            $groupsIds = 
                ArrayUtils::convertToPlainList(
                    Criteria::create(AclGroupRight::dao())->
                        setDistinct()->
                        addProjection(Projection::property('group'))->
                        add(Expression::eq('right.context', AclContext::CREDIT_REQUEST_ID))->
                        add(Expression::eq('right.action.action', AclAction::PUBLISH_ACTION))->
                        getCustomList(), 'group_id'
                );

            if ($groupsIds) {
                $users = 
                    Criteria::create(User::dao())->
                        add(Expression::in('group', $groupsIds))->
                        getList();
                
                foreach($users as $user) {
                    if ($user->getEmail()) {
                        $mailer = MimeMailSender::create('Новые изображения в запросе', 'moderatorCreditRequestNewImagesHtml', 'moderatorCreditRequestNewImagesText')->
                            setTo($user->getEmail(), $user->getName())->
                            set('listRequest', $moderatorImagesList)->
                            set('user', $user);
                        
                        foreach($moderatorImagesList as $imagesList) {
                            foreach($imagesList['list'] as $image) {
                                $mailer->setImage($image->getPath(true), $image->getFile());
                            }
                        }
                        $mailer->send();
                    }
                }
            }            
        }
        
        foreach($creditorImagesList as $userId => $creditorRequests) {
            $user = User::dao()->getById($userId);
            if ($user->getEmail()) {
                
                $mailer = MimeMailSender::create('Новые вложения в кредитном запросе', 'moderatorCreditRequestCreditorNewImagesHtml', 'moderatorCreditRequestCreditorNewImagesText')->
                    setTo($user->getEmail(), $user->getName())->
                    set('listRequest', $creditorRequests)->
                    set('user', $user);

                foreach($creditorRequests as $imagesList) {
                    foreach($imagesList['list'] as $image) {
                        $mailer->setImage($image->getPath(true), $image->getFile());
                    }
                }
                $mailer->send();
                
            }
        }
        //print_r($creditorImagesList);
        
                
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
            
            if ($creditRequest->getCreditorRequests()->isFetched() && $creditRequest->getCreditorRequests()->getCriteria()) {
                $creditRequest->getCreditorRequests()->setCriteria(Criteria::create())->clean();
            }    
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