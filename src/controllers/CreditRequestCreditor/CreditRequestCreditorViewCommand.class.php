<?

class CreditRequestCreditorViewCommand implements SecurityCommand, EditorCommand
{
    /**
     * @return CreditRequestCreditorViewCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        $subject = $form->getValue('id');
        
        if ($process) {
            if (($form->exists('accept') && $form->getValue('accept')) || ($form->exists('reject') && $form->getValue('reject'))) {
                if ($form->exists('reject') && $form->getValue('reject')) {
                    $form->getValue('id')->setStatusId(CreditRequestCreditorStatus::TYPE_REJECT);
                }
                if ($form->exists('accept') && $form->getValue('accept')) {
                    $form->getValue('id')->setStatusId(CreditRequestCreditorStatus::TYPE_CONCIDERED);
                }

                try {
                    $tr = DBPool::getByDao($subject->dao())->begin();
                    $subject = $form->getValue('id')->dao()->save($form->getValue('id'));
                    if ($form->exists('reject') && $form->getValue('reject') && $form->getValue('id')->getStatusId() == CreditRequestCreditorStatus::TYPE_REJECT) {
                        /**
                         * Отметка, что на момент отклонения запроса у КО была назначена встреча
                         */
                        $meetingOffer = null;
                        
                        foreach($form->getValue('id')->getOffers()->getList() as $offer) {
                            if ($offer->getStatusId() == CreditRequestCreditorOfferStatus::TYPE_INCOME) {
                                $offer->dao()->dropById($offer->getId());
                            } elseif (!in_array($offer->getStatusId(), array(CreditRequestCreditorOfferStatus::TYPE_CANCELED, CreditRequestCreditorOfferStatus::TYPE_REJECT))) {
                                if ($offer->getStatus()->getId() == CreditRequestCreditorOfferStatus::TYPE_MEETING) {
                                    $meetingOffer = $offer;
                                }
                                $offer->dao()->save($offer->setStatusId(CreditRequestCreditorOfferStatus::TYPE_REJECT));
                            }
                        }
                        
                        /**
                         * Отправка уведомления модератору что сделку можно было бы проверить и заемщику что встреча отменена
                         */
                        if ($meetingOffer) {
                            /**
                             * Отправка уведомления заемщику
                             */
                            if ($meetingOffer->getRequest()->getRequest()->getUser()->getEmail()) {
                                MimeMailSender::create('Отменен визит в кредитную организацию', 'creditRequestCreditorMeetingCancelUserHtml', 'creditRequestCreditorMeetingCancelUserText')->
                                    setTo($meetingOffer->getRequest()->getRequest()->getUser()->getEmail(), $meetingOffer->getRequest()->getRequest()->getUser()->getName())->
                                    set('offer', $meetingOffer)->
                                    set('user', $meetingOffer->getRequest()->getRequest()->getUser())->
                                    send();
                            }
                            $link = CommonUtils::makeUrl('creditRequestEditor', array('id' => $meetingOffer->getRequest()->getRequest()->getId(), 'action' => CommandContainer::ACTION_VIEW, 'utm_source' => 'email', 'utm_medium' => 'moderation', 'utm_campaign' => 'credit-offer-cancel'), PATH_WEB_BASE);
                            $link = trim(file_get_contents("https://clck.ru/--?url=".urlencode($link)));
                            SmsUtils::send("7{$meetingOffer->getRequest()->getRequest()->getUser()->getPhone()}", "По заявке от ".$meetingOffer->getRequest()->getRequest()->getCreatedTime()->getDay()." ".RussianTextUtils::getMonthInGenitiveCase($meetingOffer->getRequest()->getRequest()->getCreatedTime()->getMonth())." на ".number_format($meetingOffer->getRequest()->getRequest()->getSumm(), 0, '.', ' ')."руб. отменена встреча, подробнее: {$link}");
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
                            
                                        MimeMailSender::create('Отказ в заявке с назначенной встречей', 'creditRequestCreditorMeetingCancelModeratorHtml', 'creditRequestCreditorMeetingCancelModeratorText')->
                                            setTo($user->getEmail(), $user->getName())->
                                            set('offer', $meetingOffer)->
                                            set('cancelUser', $subject->getRequest()->getUser()->getId() == SecurityManager::getUser()->getId())->
                                            set('cancelCreditor', $subject->getCreditor()->getUser()->getId() == SecurityManager::getUser()->getId())->
                                            set('user', $user)->
                                            send();
                                        
                                    }
                                }
                            }
                            
                        }
                    }
                    $mav->setView(BaseEditor::COMMAND_SUCCEEDED);
                    $tr->commit();
                } catch(Exception $e) {
                    $tr->rollback();
                }
            } else {
                if ($form->exists('offer') && $form->getValue('offer')) {
                    if ($form->getValue('summ') <= 0) { $form->markWrong('summ'); } else { $form->markGood('summ'); }
                    if ($form->getValue('percents') <= 0 || $form->getValue('percents') >= 999.99) { $form->markWrong('percents'); } else { $form->markGood('percents'); } 
                    if ($form->getValue('minPeriod') && $form->getValue('maxPeriod') && $form->getValue('minPeriod') > $form->getValue('maxPeriod')) {
                        $temp = $form->getValue('maxPeriod');
                        $form->setValue('maxPeriod', $form->getValue('minPeriod'));
                        $form->setValue('minPeriod', $temp);
                    }
                    
                    if (!$form->getErrors()) {
                        $offer = CreditRequestCreditorOffer::create()->setRequest($subject);
                        FormUtils::form2object($form, $offer);
                        $offer = $offer->dao()->add($offer);
                        $mav->setView( RedirectView::create( $request->getServerVar('REQUEST_URI') ) );
                    }
                } elseif ($form->exists('investRequest') && $form->getValue('investRequest')) {
                    if ($form->getValue('investSumm') <= 0) { $form->markWrong('investSumm'); } else { $form->markGood('investSumm'); }
                    if ($form->getValue('investPercents') <= 0 || $form->getValue('investPercents') >= 999.99) { $form->markWrong('investPercents'); } else { $form->markGood('investPercents'); } 
                    if ($form->getValue('investPeriod') <= 0) { $form->markWrong('investPeriod'); } else { $form->markGood('investPeriod'); }
                    
                    if (!$form->getErrors()) {
                        $subject = 
                            $subject->dao()->save(
                                $form->getValue('id')->
                                    setInvestSumm($form->getValue('investSumm'))->
                                    setInvestPercents($form->getValue('investPercents'))->
                                    setInvestPeriod($form->getValue('investPeriod'))->
                                    dropInvestNotified()
                            );
                        $mav->setView( RedirectView::create( $request->getServerVar('REQUEST_URI') ) );
                    }                    
                }
            }
        }
        
        return $mav;
    }

    public function setForm(Form $form)
    {
        if ($form->getValue('id')->getStatus()->getId() == CreditRequestCreditorStatus::TYPE_INCOME) {
            $form->
                add(Primitive::boolean('accept'))->
                add(Primitive::boolean('reject'));
        }
        if ($form->getValue('id')->getStatus()->getId() == CreditRequestCreditorStatus::TYPE_CONCIDERED) {
            $form->
                add(Primitive::boolean('reject'));
            
            $form->
                add(Primitive::boolean('offer'))->
                add(Primitive::string('summ')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", "")))->
                add(Primitive::string('percents')->addImportFilter(Filter::pcre()->setExpression("/([^\d\.]+)/isu", "")))->
                add(Primitive::integer('minPeriod'))->
                add(Primitive::integer('maxPeriod'))->
                add(Primitive::boolean('percentsOnly'));
            
            $form->
                add(Primitive::boolean('investRequest'))->
                drop('investSumm')->drop('investPercents')->
                add(Primitive::string('investSumm')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", "")))->
                add(Primitive::string('investPercents')->addImportFilter(Filter::pcre()->setExpression("/([^\d\.]+)/isu", "")));
        }
        
        $form->drop('createdTime')->drop('request')->drop('creditor')->drop('status');
        
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof CreditRequestCreditor &&
            $form->getValue('id')->getCreditor() instanceof Creditor &&
            $form->getValue('id')->getCreditor()->getUser()->getId() == SecurityManager::getUser()->getId();
    }
}