<?

class CreditRequestViewCommand implements SecurityCommand, EditorCommand
{
    /**
     * @return CreditRequestViewCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        if ($process && $form->exists('accept') && $form->exists('reject')) {
            if ($form->getValue('reject')) {
                $form->getValue('id')->setStatusId(CreditRequestStatus::TYPE_REJECT);
            }
            if ($form->getValue('accept')) {
                $form->
                    getValue('id')->
                        setStatusId(CreditRequestStatus::TYPE_CONCIDERED)->
                        setText($form->getValue('text'));
                
                if ($form->exists('category')) {
                    $form->
                        getValue('id')->
                            setCategory($form->getValue('category'));    
                }
            }
            
            if (!$form->getErrors()) {
                try {
                    $tr = DBPool::getByDao($subject->dao())->begin();
                    $subject = $form->getValue('id')->dao()->save($form->getValue('id'));
                    if ($subject->getStatus()->getId() == CreditRequestStatus::TYPE_REJECT) {
                        SmsUtils::send("7{$subject->getUser()->getPhone()}", "Заявка от ".$subject->getCreatedTime()->getDay()." ".RussianTextUtils::getMonthInGenitiveCase($subject->getCreatedTime()->getMonth())." на ".number_format($subject->getSumm(), 0, '.', ' ')."руб. не принята к рассмотрению и отклонена");
                    } elseif ($subject->getStatus()->getId() == CreditRequestStatus::TYPE_CONCIDERED) {
                        $creditorIds = 
                            ArrayUtils::convertToPlainList(
                                Criteria::create(CreditorCategory::dao())->
                                    setDistinct()->
                                    addProjection(Projection::property('creditor'))->
                                    getCustomList(), 'creditor_id'
                            );
                        if ($creditorIds) {
                            $users = array();
                            $creditorList = 
                                Criteria::create(Creditor::dao())->
                                    add(Expression::isTrue('active'))->
                                    add(Expression::isFalse('deleted'))->
                                    add(Expression::in('id', $creditorIds))->
                                    getList();

                            foreach($creditorList as $creditor) {
                                if ($creditor->getUser()->getEmail() && !isset($users[$creditor->getUser()->getId()])) {
                                    $users[$creditor->getUser()->getId()] = true;
                                    /**
                                     * Здесь конечно было бы неплохо потом сделать сначала формирование запросов по пользователям, а потом в одном письме пользователю
                                     * отправлять уведомление со ссылками на его конкретные заявки для каждой кредитной организации
                                     */
                                    MimeMailSender::create('Новая заявка на кредит', 'creditRequestCreditorNewHtml', 'creditRequestCreditorNewText')->
                                        setTo($creditor->getUser()->getEmail(), $creditor->getUser()->getName())->
                                        set('request', $subject)->
                                        set('user', $creditor->getUser())->
                                        send();
                                }

                                $creditorRequest = 
                                    CreditRequestCreditor::create()->
                                        setRequest($subject)->
                                        setCreditor($creditor)->
                                        setStatusId(CreditRequestCreditorStatus::TYPE_INCOME)->
                                        setExpired(Timestamp::create(sprintf("+%d hour", Constants::CREDIT_REQUEST_CREDITOR_INCOME_LIFETIME)));
                                $creditorRequest->dao()->add($creditorRequest);
                            }
                            SmsUtils::send("7{$subject->getUser()->getPhone()}", "Заявка от ".$subject->getCreatedTime()->getDay()." ".RussianTextUtils::getMonthInGenitiveCase($subject->getCreatedTime()->getMonth())." на ".number_format($subject->getSumm(), 0, '.', ' ')."руб. передана кредитным организациям.");
                        }
                    }
                    $mav->setView(BaseEditor::COMMAND_SUCCEEDED);
                    $tr->commit();
                } catch(Exception $e) {
                    $tr->rollback();
                }
            }
        }

        if ($mav->getView() != EditorController::COMMAND_SUCCEEDED && $form->exists('accept')) {

            $categoryList = 
                Criteria::create(Category::dao())->
                    addOrder(OrderBy::create('sort')->asc())->
                    getList();

            $mav->
                getModel()->
                    set('categoryList', $categoryList);
        }
        
        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'action', 'go', 'return', 'cancel', 'securityCode');
        if (!$form->getValue('id')->isDeleted() && $form->getValue('id')->getStatus()->getId() == CreditRequestStatus::TYPE_INCOME && SecurityManager::isAllowedAction(AclAction::PUBLISH_ACTION, AclContext::CREDIT_REQUEST_ID)) {
            $neededPrimitives[] = 'text';
            $form->get('text')->addImportFilter(Filter::textImport())->addImportFilter(Filter::pcre()->setExpression("/(\\r?\\n){2,}/isu", "\r\n"))->addDisplayFilter(Filter::htmlSpecialChars())->required();
            $form->
                add(Primitive::boolean('accept'))->
                add(Primitive::boolean('reject'));
            $neededPrimitives[] = 'accept';
            $neededPrimitives[] = 'reject';
            $neededPrimitives[] = 'category';
        }
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        if ($form->exists('category')) { $form->get('category')->required(); }
        if ($form->exists('text')) { $form->get('text')->optional(); }
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof CreditRequest &&
            $form->getValue('id')->checkPermissions(AclAction::VIEW_ACTION);
    }
}