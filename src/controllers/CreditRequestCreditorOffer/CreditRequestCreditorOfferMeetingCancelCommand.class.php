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
                Mail::create()->
                    setTo($subject->getRequest()->getCreditor()->getUser()->getEmail())->
                    setFrom(DEFAULT_FROM)->
                    setSubject('Отменена встреча с заемщиком')->
                    setText("По кредитной заявке отменена встреча с заемщиком на {$oldData->getDate()->toFormatString('d.m')} ".sprintf('%02d:%02d', $oldData->getTime()->getHour(), $oldData->getTime()->getMinute())."\r\n\r\nПосмотреть заявку: ".CommonUtils::makeUrl('creditRequestEditor', array('action' => CommandContainer::ACTION_VIEW, 'id' => $subject->getRequest()->getId()), PATH_WEB_CREDITOR))->
                    send();
            }
            /**
             * Отправка уведомления заемщику
             */
            if ($subject->getRequest()->getRequest()->getUser()->getId() != SecurityManager::getUser()->getId() && $subject->getRequest()->getRequest()->getUser()->getEmail()) {
                Mail::create()->
                    setTo($subject->getRequest()->getRequest()->getUser()->getEmail())->
                    setFrom(DEFAULT_FROM)->
                    setSubject('Отменен визит в кредитную организацию')->
                    setText("По кредитной заявке отменена встреча на {$oldData->getDate()->toFormatString('d.m')} ".sprintf('%02d:%02d', $oldData->getTime()->getHour(), $oldData->getTime()->getMinute()).". Посмотреть заявку: ".CommonUtils::makeUrl('creditRequestEditor', array('action' => CommandContainer::ACTION_VIEW, 'id' => $subject->getRequest()->getRequest()->getId()), PATH_WEB_BASE))->
                    send();
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
                            Mail::create()->
                                setTo($user->getEmail())->
                                setFrom(DEFAULT_FROM)->
                                setSubject('Отменен визит в кредитную организацию')->
                                setText(
                                    ($subject->getRequest()->getCreditor()->getUser()->getId() == SecurityManager::getUser()->getId() ? "Представитель кредитной организации" : "Заемщик").
                                    "отменил встречу на {$oldData->getDate()->toFormatString('d.m')} ".sprintf('%02d:%02d', $oldData->getTime()->getHour(), $oldData->getTime()->getMinute()).".\r\n\r\nПосмотреть Заявку: ".CommonUtils::makeUrl('creditRequestEditor', array('action' => CommandContainer::ACTION_VIEW, 'id' => $subject->getRequest()->getRequest()->getId()), PATH_WEB_ADMIN)
                                )->
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