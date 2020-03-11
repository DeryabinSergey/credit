<?

class CreditRequestCreditorOfferMeetingCancelCommand extends SaveCommand implements SecurityCommand
{
    /**
     * @return CreditRequestCreditorOfferMeetingCancelCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = ModelAndView::create();
            
        $form->getValue('id')->setStatusId(CreditRequestCreditorOfferStatus::TYPE_INCOME);
        $oldData = clone $form->getValue('id');
        $form->markGood('id');
        $form->getValue('id')->dropTime()->dropDate();
        
        $mav = parent::run($subject, $form, $request);
        
        if ($mav->getView() == EditorController::COMMAND_SUCCEEDED) {
            /**
             * Отправка уведомления кредитной организации
             */
            if ($subject->getRequest()->getCreditor()->getUser()->getId() != SecurityManager::getUser()->getId() && $subject->getRequest()->getCreditor()->getUser()->getEmail()) {
                MimeMailSender::create('Отменена встреча с заемщиком', 'creditRequestCreditorMeetingCancelCreditorHtml', 'creditRequestCreditorMeetingCancelCreditorText')->
                    setTo($subject->getRequest()->getCreditor()->getUser()->getEmail(), $subject->getRequest()->getCreditor()->getUser()->getName())->
                    set('offer', $oldData)->
                    set('user', $subject->getRequest()->getCreditor()->getUser())->
                    send();
            }
            /**
             * Отправка уведомления заемщику
             */
            if ($subject->getRequest()->getRequest()->getUser()->getId() != SecurityManager::getUser()->getId()) {
                if ($subject->getRequest()->getRequest()->getUser()->getEmail()) {
                    MimeMailSender::create('Отменен визит в кредитную организацию', 'creditRequestCreditorMeetingCancelUserHtml', 'creditRequestCreditorMeetingCancelUserText')->
                        setTo($subject->getRequest()->getRequest()->getUser()->getEmail(), $subject->getRequest()->getRequest()->getUser()->getName())->
                        set('offer', $oldData)->
                        set('user', $subject->getRequest()->getRequest()->getUser())->
                        send();
                }
                $link = CommonUtils::makeUrl('creditRequestEditor', array('id' => $subject->getRequest()->getRequest()->getId(), 'action' => CommandContainer::ACTION_VIEW, 'utm_source' => 'email', 'utm_medium' => 'moderation', 'utm_campaign' => 'credit-offer-cancel'), PATH_WEB_BASE);
                $link = trim(file_get_contents("https://clck.ru/--?url=".urlencode($link)));
                SmsUtils::send("7{$subject->getRequest()->getRequest()->getUser()->getPhone()}", "По заявке от ".$subject->getRequest()->getRequest()->getCreatedTime()->getDay()." ".RussianTextUtils::getMonthInGenitiveCase($subject->getRequest()->getRequest()->getCreatedTime()->getMonth())." на ".number_format($subject->getRequest()->getRequest()->getSumm(), 0, '.', ' ')."руб. отменена встреча, подробнее: {$link}");
            }
            /**
             * Отправка уведомления модератору -  пользователям с правами на публикацию заявлений на кредит
             */
            if (
                $subject->getRequest()->getCreditor()->getUser()->getId() == SecurityManager::getUser()->getId() ||
                $subject->getRequest()->getRequest()->getUser()->getId() == SecurityManager::getUser()->getId()
            ) {
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
                            
                            MimeMailSender::create('Отменена встреча с заемщиком', 'creditRequestCreditorMeetingCancelModeratorHtml', 'creditRequestCreditorMeetingCancelModeratorText')->
                                setTo($user->getEmail(), $user->getName())->
                                set('offer', $oldData)->
                                set('cancelUser', $subject->getRequest()->getRequest()->getUser()->getId() == SecurityManager::getUser()->getId())->
                                set('cancelCreditor', $subject->getRequest()->getCreditor()->getUser()->getId() == SecurityManager::getUser()->getId())->
                                set('user', $user)->
                                send();
                            
                        }
                    }
                }
            }
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            drop('request')->
            drop('status')->
            drop('createdTime')->
            drop('summ')->
            drop('minPeriod')->
            drop('maxPeriod')->
            drop('percents')->
            drop('percentsOnly')->
            drop('address')->
            drop('text')->
            drop('date')->
            drop('time');
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        /**
         * Отменить встречу может только или кредитная организация или заемщик или модератор с правами на публикацию кредитных заявок
         */
        return 
            $form->getValue('id')->getStatus()->getId() == CreditRequestCreditorOfferStatus::TYPE_MEETING &&
            (
                $form->getValue('id')->getRequest()->getCreditor()->getUser()->getId() == SecurityManager::getUser()->getId() ||
                $form->getValue('id')->getRequest()->getRequest()->getUser()->getId() == SecurityManager::getUser()->getId() ||
                SecurityManager::isAllowedAction(AclAction::PUBLISH_ACTION, AclContext::CREDIT_REQUEST_ID)
            );
    }
}