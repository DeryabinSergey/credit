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
        $userPhone = Session::exist(creditRequestEditor::SESSION_PHONE) ? Session::get(creditRequestEditor::SESSION_PHONE) : false;
        $userExists = SecurityManager::getUser();
        
        if ($userExists instanceof User || $userPhone) {
            
            if ($userExists instanceof User) {
                $userPhone = $userExists->getPhone();
            } else {
                try {
                    $userExists = User::dao()->getByLogic(Expression::eq('phone', $userPhone));
                } catch(ObjectNotFoundException $e) { /* nothin here */ }
            }

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
                    
                    if (!$userExists instanceof User) {
                        $password = ViewTextUtils::createRandomPassword(10);
                    
                        $userExists = 
                            User::dao()->
                                add(
                                    User::create()->
                                        setName($form->getValue('name'))->
                                        setPassword(hash('sha256', $password))->
                                        setPhone($userPhone)->
                                        setActive(true)
                                );
                        
                        SmsUtils::send("7{$userPhone}", "Ваш пароль для входа на сайт: {$password}");
                        SecurityManager::setUser($userExists, true, $request);
                    } elseif ($userExists instanceof User && !SecurityManager::isAuth()) {
                        SecurityManager::setUser($userExists, true, $request);
                    }
                    FormUtils::form2object($form, $subject);
                    $subject = $subject->dao()->add($subject->setUser($userExists));
                    if (Session::exist(creditRequestEditor::SESSION_PHONE)) Session::drop(creditRequestEditor::SESSION_PHONE);
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
                        set('userPhone', $userPhone)->
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
        $form->add(Primitive::string('profit')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", ""))->required());
        $form->add(Primitive::string('passport')->setAllowedPattern("/^\d{10}/is")->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", "")));
        $form->get('name')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars());
        $form->get('text')->addImportFilter(Filter::textImport())->addImportFilter(Filter::pcre()->setExpression("/(\\r?\\n){2,}/isu", "\r\n"))->addDisplayFilter(Filter::htmlSpecialChars())->required();
        
        return $this;
    }

}