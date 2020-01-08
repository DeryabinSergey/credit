<?

class UserUpdateCommand extends SaveCommand implements SecurityCommand
{
    const ERROR_INTERNAL = 0x0004;
    
    protected $banList = 
            array(
                1   => '1 час',
                2   => '2 часа',
                4   => '4 часа',
                6   => '6 часов',
                12  => '12 часов',
                24  => '1 день',
                48  => '2 дня',
                72  => '3 дня',
                168 => '1 неделя',
                336 => '2 недели',
                720 => '1 месяц',
                1440    => '2 месяца',
                4320    => '6 месяцев',
                8760    => 'год',
                0       => 'навсегда'
            );
    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();

        if ($process && !$form->getErrors()) {
            
            if ($form->getValue('ban')) {
                if ($form->getValue('forceBan')) {
                    if ($form->getValue('banList')) {
                        $form->getValue('id')->setBanExpire(Timestamp::create("+{$form->getValue('banList')} hour"));
                    } else {
                        $form->getValue('id')->dropBanExpire();
                    }
                } else {
                    $form->get('banComment')->setValue($form->getValue('id')->getBanComment());
                }
            } else {
                $form->getValue('id')->dropBanExpire();
                $form->get('banComment')->dropValue();
            }

            try {
                $tr = InnerTransaction::begin($subject->dao());
                $mav = parent::run($subject, $form, $request);
                if ($mav->getView() == BaseEditor::COMMAND_SUCCEEDED) {
                    if ($form->getValue('forceBan') && $form->getValue('ban')) {

                        $text = 
                            "Ваша учетная запись заблокирована".($subject->getBanExpire()?'':' навсегда').".\r\n\r\n".
                            ($subject->getBanExpire() instanceof Timestamp ? "Блокировка будет снята {$subject->getBanExpire()->getDay()} ".RussianTextUtils::getMonthInGenitiveCase($subject->getBanExpire()->getMonth())." в {$subject->getBanExpire()->getHour()}:{$subject->getBanExpire()->getMinute()}.\r\n\r\n" : "").
                            ($subject->getBanComment()?"Комментарий администрации портала: {$subject->getBanComment()}":'');
                    
                    Mail::create()->
                        setTo($subject->getEmail())->
                        setFrom(DEFAULT_FROM)->
                        setSubject('Уведомление о блокировке')->
                        setText($text)->
                        send();                        
                    }
                }
                $tr->commit();
            } catch(Exception $e) {
                print_r($e->getMessage());
                $tr->rollback();
                $form->markCustom('id', self::ERROR_INTERNAL);
                error_log("Ошибка при сохранении данных пользователя: {$e->getMessage()} \n\n{$e->getTraceAsString()}");
            }
        }
        
        if ($mav->getView() != BaseEditor::COMMAND_SUCCEEDED) {
            
            if ($form->exists('group')) {
                $list = Criteria::create(AclGroup::dao())->addOrder(OrderBy::create('name')->asc())->getList();
                
                $mav->
                    getModel()->
                        set('groupList', $list);
            }
            $mav->
                getModel()->
                    set('banList', $this->banList);
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $neededPrimitives = array('id', 'name', 'ban', 'banComment', 'action', 'return', 'cancel', 'securityCode', 'securityType');
        if (SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::ACL_ID)) { $neededPrimitives[] = 'group'; }
        foreach($form->getPrimitiveNames() as $primitive) {
            if (!in_array($primitive, $neededPrimitives)) {
                $form->drop($primitive);
            }
        }
        
        $form->
            get('name')->
                addImportFilter(Filter::textImport())->
                addImportFilter(Filter::htmlSpecialChars())->
                required();
        
        $form->
            get('banComment')->
                addImportFilter(Filter::textImport())->
                addImportFilter(Filter::htmlSpecialChars());
        
        $form->
            add(Primitive::choice('banList')->setList($this->banList)->setDefault(1))->
            add(Primitive::integer('forceBan')->setMin(0)->setMax(1)->setDefault(0));
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::USER_ID);
    }
}