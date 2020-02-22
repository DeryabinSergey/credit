<?php

class CreditRequestConfirmCommand implements EditorCommand
{
    const ERROR_INTERNAL    = 0x0003;
    const ERROR_DUPLICATE   = 0x0004;
    const ERROR_BAN         = 0x0005;
    
    const ERROR_INVALID     = 0x0003;
    const ERROR_YONG        = 0x0003;

    /**
     * @return CreditRequestConfirmCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $form->markGood('id');
        $subject = CreditRequest::create();
        $userExists = SecurityManager::getUser();        
        
        if ($userExists instanceof User) {
                
            $imagesList = 
                Criteria::create(CreditRequestImage::dao())->
                    add(Expression::isNull('owner'))->
                    add(Expression::eq('user', SecurityManager::getUser()->getId()))->
                    addOrder(OrderBy::create('sort')->asc())->
                    getList();

            if ($process && !$form->getErrors()) {
                
                if ($form->getValue('type')->getId() == SubjectType::TYPE_YUR) {
                    $form->get('birthDate')->dropValue();
                    $form->get('passport')->dropValue();
                    $form->markGood('passport')->markGood('birthDate');
                    if (!$form->getValue('ogrn')) { $form->markMissing('ogrn'); }
                } else {
                    if (!$form->getValue('birthDate')) $form->markMissing('birthDate');
                    if (!$form->getValue('passport')) $form->markMissing('passport');
                    if ($form->getValue('type')->getId() == SubjectType::TYPE_FIZ) {
                        $form->get('ogrn')->dropValue();
                        $form->markGood('ogrn');
                    }
                }
                
                if (!$form->getErrors() && $form->getValue('type')->getId() != SubjectType::TYPE_YUR && Timestamp::compare($form->getValue('birthDate'), Timestamp::create("-18 year")) > 0) {
                    $form->markCustom('birthDate', self::ERROR_YONG);
                }
                
                if (!$form->getErrors() && $form->getValue('type')->getId() != SubjectType::TYPE_YUR) {
                    $req = json_encode(array($form->getValue('passport')));
                    $head = "Content-Type: application/json\r\nAccept: application/json\r\nAuthorization: Token ".Constants::DADATA_TOKEN."\r\nX-Secret: ".Constants::DADATA_SECRET . PHP_EOL;
                    try {
                        $response = json_decode(file_get_contents("https://cleaner.dadata.ru/api/v1/clean/passport", false, stream_context_create( array('http' => array('method' => 'POST', 'header' => $head, 'content' => $req) ) )), "true");
                        if ($response && is_array($response) && isset($response[0]) && is_array($response[0]) && isset($response[0]['qc']) && $response[0]['qc'] != 0) {
                            $form->markCustom('passport', self::ERROR_INVALID);
                        }
                    } catch(Exception $e) { 
                        /**
                         * @fixme
                         * Здесть при ошибке или недоступности сервиса надо бы ставить отметку что данные паспорта не проверены.
                         * Наверняка все долбоебы привыкнут и будут считать эти данные достоверными
                         */
                    }
                }
                
                
                if (!$form->getErrors()) {
                    
                    FormUtils::form2object($form, $subject);
                    $subject = $subject->dao()->add($subject->setUser($userExists));
                    foreach($imagesList as $image) {
                        $image->dao()->save($image->dropUser()->setOwner($subject));
                    }
                    
                    /**
                     * Отправка уведомлений пользователям с правами на публикацию заявлений на кредит
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
                                    setSubject('Новая заявка на кредит')->
                                    setText("Поступила новая заявка на кредит.\r\n\r\nПосмотреть все заявки ожидающие обработки: ".CommonUtils::makeUrl('creditRequestList', array('status' => array(CreditRequestStatus::TYPE_INCOME), 'delete' => -1), PATH_WEB_ADMIN))->
                                    send();
                            }
                        }
                    }
                    
                    $mav->setView(EditorController::COMMAND_SUCCEEDED);
                    
                }
            }

            if ($mav->getView() != EditorController::COMMAND_SUCCEEDED) {
                
                $categoryList = 
                    Criteria::create(Category::dao())->
                        addOrder(OrderBy::create('sort')->asc())->
                        getList();
                
                $mav->
                    getModel()->
                        set('categoryList', $categoryList)->
                        set('imagesList', $imagesList)->
                        set('userExists', $userExists);
            }
        } else {
            $mav->
                setView(RedirectView::create(CommonUtils::makeUrl('creditRequestEditor', array('action' => creditRequestEditor::ACTION_START, 'return' => $form->getValue('return')))))->
                getModel()->clean();
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'type', 'category', 'name', 'birthDate', 'ogrn', 'text', 'securityCode', 'action', 'go', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->get('birthDate')->optional();
        $form->add(Primitive::string('summ')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", ""))->required());
        $form->add(Primitive::string('passport')->setAllowedPattern("/^\d{10}/is")->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", "")));
        $form->get('name')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars());
        $form->get('text')->addImportFilter(Filter::textImport())->addImportFilter(Filter::pcre()->setExpression("/(\\r?\\n){2,}/isu", "\r\n"))->addDisplayFilter(Filter::htmlSpecialChars())->required();
        
        return $this;
    }

}