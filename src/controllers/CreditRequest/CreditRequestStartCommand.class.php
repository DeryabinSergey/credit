<?php

class CreditRequestStartCommand implements EditorCommand
{
    const ERROR_INTERNAL        = 0x0003;
    const ERROR_DUPLICATE       = 0x0004;
    const ERROR_BAN             = 0x0005;
    const ERROR_AUTH_ENABLED    = 0x0006;
    
    const ERROR_EXPIRED         = 0x0003;
    const ERROR_INVALID     = 0x0003;
    const ERROR_YONG        = 0x0003;
    /**
     * @return CreditRequestStartCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $form->markGood('id');
        $subject = CreditRequest::create();
        $userExists = SecurityManager::getUser();
        $confirm = null;
        
        if (!SecurityManager::isAuth() && !SecurityManager::isAuthEnabled()) { $form->markCustom('phone', self::ERROR_AUTH_ENABLED); }
        
        
        if ($process && !SecurityManager::isAuth()) {    
            
            /** Отправляем код подтверждения, если чувак не додумался нажать на кнопку отправить код **/
            if (!$form->getError('phone') && $form->getValue('phone')) {
                try {
                    $confirm = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_CREDIT_REQUEST), Expression::eq('phone', $form->getValue('phone'))));
                } catch (ObjectNotFoundException $e) {
                    $confirm = 
                        Confirm::dao()->add(
                            Confirm::create()->
                                setType(ConfirmType::create(ConfirmType::TYPE_CREDIT_REQUEST))->
                                setPhone($form->getValue('phone'))->
                                setCode(random_int(1, 9999))
                        );
                    SmsUtils::send("7{$form->getValue('phone')}", sprintf("Код подтверждения для оформления заявки на кредит: %04d", $confirm->getCode()));
                } 
            }
            
            if (!$form->getValue('accept')) { 
                $form->markWrong('accept');
            } elseif ($form->getValue('code')) {

                $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, stream_context_create( array('http' => array('method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => http_build_query(array('secret' => GOOGLE_RECAPTCHA_CLOSED, 'response' => $form->getValue('response')))) ) )), "true");
                if (!$response['success']) {
                    $form->markWrong('response');
                } else {

                    try {
                        if (Timestamp::compare($confirm->getExpiredTime(), Timestamp::makeNow()) == -1 || $confirm->getTry() >= 3) throw new ObjectNotFoundException();
                        if ($confirm->getCode() != $form->getValue('code')) {
                            $form->markWrong('code');
                            $confirm->dao()->save($confirm->setTry($confirm->getTry() + 1));
                        } else {
                            try {
                                $userExists = User::dao()->getByLogic(Expression::eq('phone', $form->getValue('phone')));
                            } catch(ObjectNotFoundException $e) { 
                                
                                $password = ViewTextUtils::createRandomPassword(10);

                                $userExists = 
                                    User::dao()->
                                        add(
                                            User::create()->
                                                setName($form->getValue('phone'))->
                                                setPassword(hash('sha256', $password))->
                                                setPhone($form->getValue('phone'))->
                                                setActive(true)
                                        );

                                SmsUtils::send("7{$form->getValue('phone')}", "Ваш пароль для входа на сайт: {$password}");                                
                                
                            }
                            
                            if ($userExists->isBan()) {
                                $form->markCustom('phone', self::ERROR_BAN);
                            } else {
                                SecurityManager::setUser($userExists, true, $request);
                                $form->drop('phone')->drop('code')->drop('accept')->drop('response');
                            }
                            $confirm->dao()->dropById($confirm->getId());
                        }

                    } catch (ObjectNotFoundException $e) {
                        $form->markCustom('code', self::ERROR_EXPIRED);
                    }
                }                
            }
        }
        
        if ($process && !$form->getErrors()) {
                
                if (!$form->getErrors() && Timestamp::compare($form->getValue('birthDate'), Timestamp::create("-18 year")) > 0) {
                    $form->markCustom('birthDate', self::ERROR_YONG);
                }
                
                if (!$form->getErrors() && (!defined('__LOCAL_DEBUG__') || __LOCAL_DEBUG__ == false)) {
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
                    $subject = $subject->dao()->add($subject->setUser($userExists)->setType(SubjectType::create(SubjectType::TYPE_FIZ)));
                    
                    /**
                     * Если именем пользователя было установлен номер телефона на предыдущем шаге (заявка без регистрации) - 
                     * пытаемся здесь получить имя пользователя
                     */
                    if (
                        !$userExists->getEmail() && 
                        $userExists->getName() == $userExists->getPhone() && 
                        $subject->getName()
                    ) {
                        $userName = explode(" ", $subject->getName(), $subject->getType()->isIP() ? 3 : 2);
                        if (
                            ($subject->getType()->isIP() && count($userName) == 3 && $userName[2]) ||
                            ($subject->getType()->isFiz() && count($userName) == 2 && $userName[1])
                        ) {
                            $userName = $userName[$subject->getType()->isIP() ? 2 : 1];
                            $userExists->dao()->save($userExists->setName($userName));
                        }
                        
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
                        
                        $telegram = TelegramSender::create('creditRequestNew')->set('request', $subject);
                        
                        foreach($users as $user) {
                            
                            if ($user->getEmail()) {
                                MimeMailSender::create('Новая заявка на кредит', 'creditRequestNewHtml', 'creditRequestNewText')->
                                    setTo($user->getEmail(), $user->getName())->
                                    set('request', $subject)->
                                    set('user', $user)->
                                    send();
                            }
                            
                            if ($user->getTelegramId() && $user->isTelegramBotEnabled()) {
                                try {
                                    $telegram->send($user->getTelegramId());
                                } catch(BaseException $e) {
                                    if (mb_stripos($e->getMessage(), "403 Forbidden")) {
                                        $user->dao()->save($user->setTelegramBotEnabled(false));
                                    }
                                }
                            }
                        }
                    }
                    
                    $mav->setView(EditorController::COMMAND_SUCCEEDED);
                    
                }
        }

        if ($mav->getView() != EditorController::COMMAND_SUCCEEDED) {
            $mav->
                getModel()->
                    set('userExists', $userExists);
        }
        
        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'securityCode', 'name', 'birthDate', 'category', 'action', 'go', 'return', 'cancel');
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        if (!SecurityManager::isAuth()) {
            $form->add(Primitive::boolean('accept')->required());
            $form->add(Primitive::string('phone')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/", ""))->setAllowedPattern("/\d{10}/is")->required());
            $form->add(Primitive::integer('code')->setMax(9999)->setMin(1)->required());
            $form->add(Primitive::string('response')->required());
        }
        
        $form->get('birthDate')->required();
        $form->add(Primitive::string('summ')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", ""))->required());
        $form->add(Primitive::string('passport')->setAllowedPattern("/^\d{10}/is")->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", ""))->required());
        $form->get('name')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars())->required();
        
        
        return $this;
    }

}