<?

class CreditRequestCreditorOfferCreditorAcceptCommand extends SaveCommand implements SecurityCommand
{
    /**
     * @return CreditRequestCreditorOfferMeetingCancelCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = ModelAndView::create();
            
        $form->getValue('id')->setStatusId(CreditRequestCreditorOfferStatus::TYPE_SUCCESS);
        $oldData = clone $form->getValue('id');
        $form->markGood('id');
        $form->getValue('id')->dropTime()->dropDate();
        
        $mav = parent::run($subject, $form, $request);
        
        if ($mav->getView() == EditorController::COMMAND_SUCCEEDED) {
            
            /**
             * Переводим весь запрос в статус оформлено
             */
            $subject->getRequest()->getRequest()->dao()->save($subject->getRequest()->getRequest()->setStatusId(CreditRequestStatus::TYPE_SUCCESS));
            /**
             * Работаем с запросами к кредитным организациям
             */
            if ($subject->getRequest()->getRequest()->getCreditorRequests()->isFetched() && $subject->getRequest()->getRequest()->getCreditorRequests()->getCriteria()) {
                $subject->getRequest()->getRequest()->getCreditorRequests()->setCriteria(Criteria::create())->clean();
            }
            foreach($subject->getRequest()->getRequest()->getCreditorRequests()->getList() as $creditorRequest) {
                /**
                 * Текущий запрос к кредитной организации, которая выдала и оформила займ
                 */
                if ($subject->getRequest()->getId() == $creditorRequest->getId()) {
                    $creditorRequest->dao()->save($creditorRequest->setStatusId(CreditRequestCreditorStatus::TYPE_SUCCESS));
                    foreach($creditorRequest->getOffers()->getList() as $offer) {
                        if ($offer->getId() != $subject->getId()) {
                            $offer->dao()->dropById($offer->getId());
                        }
                    }
                } else {
                    $creditorRequest->dao()->save($creditorRequest->setStatusId(CreditRequestCreditorStatus::TYPE_CANCELED));
                    foreach($creditorRequest->getOffers()->getList() as $offer) {
                        if ($offer->getStatusId() == CreditRequestCreditorOfferStatus::TYPE_INCOME) {
                            $offer->dao()->dropById($offer->getId());
                        } elseif ($offer->getStatusId() != CreditRequestCreditorOfferStatus::TYPE_REJECT) {
                            $offer->dao()->save($offer->setStatusId(CreditRequestCreditorOfferStatus::TYPE_CANCELED));
                        }
                    }
                }
            }
            
            /**
             * Отправка уведомления модератору -  пользователям с правами на публикацию заявлений на кредит
             */
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
                            setSubject('Оформлен займ')->
                            setText("По кредитной заявке состоялась сделка!\r\n\r\nПосмотреть Заявку: ".CommonUtils::makeUrl('creditRequestEditor', array('action' => CommandContainer::ACTION_VIEW, 'id' => $subject->getRequest()->getRequest()->getId()), PATH_WEB_ADMIN))->
                            send();
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
         * Акцептовать сделку может только или кредитная организация или модератор с правами на публикацию кредитных заявок
         */
        return 
            (
                $form->getValue('id')->getStatus()->getId() == CreditRequestCreditorOfferStatus::TYPE_MEETING ||
                $form->getValue('id')->getRequest()->hasMeetingOffers()
            ) &&
            (
                $form->getValue('id')->getRequest()->getCreditor()->getUser()->getId() == SecurityManager::getUser()->getId() ||
                SecurityManager::isAllowedAction(AclAction::PUBLISH_ACTION, AclContext::CREDIT_REQUEST_ID)
            );
    }
}