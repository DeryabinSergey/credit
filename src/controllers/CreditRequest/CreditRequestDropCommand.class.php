<?

class CreditRequestDropCommand implements SecurityCommand, EditorCommand
{
    const ERROR_EXTERNAL = 0x0003;

    /**
     * @return CreditRequestDropCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = ModelAndView::create();

        if ($form->getValue('ok')) {
            try {
                $id = $form->getValue('id')->getId();                
                $form->getValue('id')->dao()->markAsDeleted($form->getValue('id'));
                foreach($form->getValue('id')->getCreditorRequests()->getList() as $creditorRequest) {
                    if ($creditorRequest->getStatus()->getId() == CreditRequestCreditorStatus::TYPE_INCOME) {
                        $creditorRequest->dao()->drop($creditorRequest);
                    } elseif (in_array($creditorRequest->getStatus()->getId(), array(CreditRequestCreditorStatus::TYPE_CONCIDERED))) {
                        $creditorRequest->dao()->save($creditorRequest->setStatusId(CreditRequestCreditorStatus::TYPE_CANCELED));
                    }
                }
                
                $meetingOffer = null;
                foreach($form->getValue('id')->getCreditorOffers(array(), array(CreditRequestCreditorOfferStatus::TYPE_CANCELED, CreditRequestCreditorOfferStatus::TYPE_REJECT, CreditRequestCreditorOfferStatus::TYPE_SUCCESS)) as $offer) {
                    if ($offer->getStatus()->getId() == CreditRequestCreditorOfferStatus::TYPE_MEETING) {
                        $meetingOffer = $offer;
                    }
                    $offer->dao()->save($offer->setStatusId(CreditRequestCreditorOfferStatus::TYPE_CANCELED));
                }
                
                if ($meetingOffer) {
                    /**
                     * Отправка уведомления кредитной организации
                     */
                    if ($meetingOffer->getRequest()->getCreditor()->getUser()->getEmail()) {
                        Mail::create()->
                            setTo($meetingOffer->getRequest()->getCreditor()->getUser()->getEmail())->
                            setFrom(DEFAULT_FROM)->
                            setSubject('Отменена встреча с заемщиком')->
                            setText("По кредитной заявке отменена встреча с заемщиком на {$meetingOffer->getDate()->toFormatString('d.m')} ".sprintf('%02d:%02d', $meetingOffer->getTime()->getHour(), $meetingOffer->getTime()->getMinute())."\r\n\r\nПосмотреть заявку: ".CommonUtils::makeUrl('creditRequestEditor', array('action' => CommandContainer::ACTION_VIEW, 'id' => $meetingOffer->getRequest()->getId()), PATH_WEB_CREDITOR))->
                            send();
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
                                    setSubject('Отменен визит в кредитную организацию')->
                                    setText("Заемщик удалил запрос с назначенной встречей на {$meetingOffer->getDate()->toFormatString('d.m')} ".sprintf('%02d:%02d', $meetingOffer->getTime()->getHour(), $meetingOffer->getTime()->getMinute()).".\r\n\r\nПосмотреть Заявку: ".CommonUtils::makeUrl('creditRequestEditor', array('action' => CommandContainer::ACTION_VIEW, 'id' => $meetingOffer->getRequest()->getRequest()->getId()), PATH_WEB_ADMIN))->
                                    send();
                            }
                        }
                    }                
                }
                
                return
		    $mav->
                        setView(EditorController::COMMAND_SUCCEEDED)->
                        setModel(Model::create()->set('id', $id));                
            } catch(DatabaseException $e) {
                $form->markCustom('id', self::ERROR_EXTERNAL);
            }
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->add(Primitive::boolean('ok'));
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof CreditRequest &&
            $form->getValue('id')->checkPermissions(AclAction::DELETE_ACTION);
    }
}