<?

class CreditRequestCreditorOfferMeetingCommand extends SaveCommand implements SecurityCommand
{
    protected $returnStatus = false;
    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
            
        if ($process) {
            
            if ($form->getValue('reject')) {
                $this->returnStatus = $form->getValue('reject');
                $form->dropAllErrors();
            } else {
                $form->getValue('id')->setStatusId(CreditRequestCreditorOfferStatus::TYPE_MEETING);
            }

            $mav = parent::run($subject, $form, $request);
            
            if ($form->getValue('accept') && $mav->getView() == EditorController::COMMAND_SUCCEEDED) {
                /**
                 * Отправка уведомления кредитной организации
                 */                
                if ($subject->getRequest()->getCreditor()->getUser()->getEmail()) {
                    MimeMailSender::create('Назначена встреча с заемщиком', 'creditRequestCreditorMeetingCreditorHtml', 'creditRequestCreditorMeetingCreditorText')->
                        setTo($subject->getRequest()->getCreditor()->getUser()->getEmail(), $subject->getRequest()->getCreditor()->getUser()->getName())->
                        set('offer', $subject)->
                        set('user', $subject->getRequest()->getCreditor()->getUser())->
                        send();
                }
                /**
                 * Отправка уведомления заемщику
                 */
                if ($subject->getRequest()->getRequest()->getUser()->getEmail()) {
                    MimeMailSender::create('Назначен визит в кредитную организацию', 'creditRequestCreditorMeetingUserHtml', 'creditRequestCreditorMeetingUserText')->
                        setTo($subject->getRequest()->getRequest()->getUser()->getEmail(), $subject->getRequest()->getRequest()->getUser()->getName())->
                        set('offer', $subject)->
                        set('user', $subject->getRequest()->getRequest()->getUser())->
                        send();
                }
                $link = CommonUtils::makeUrl('creditRequestEditor', array('id' => $subject->getRequest()->getRequest()->getId(), 'action' => CommandContainer::ACTION_VIEW, 'utm_source' => 'email', 'utm_medium' => 'moderation', 'utm_campaign' => 'credit-offer-user-meeting'), PATH_WEB_BASE);
                $link = trim(file_get_contents("https://clck.ru/--?url=".urlencode($link)));
                SmsUtils::send("7{$subject->getRequest()->getRequest()->getUser()->getPhone()}", "По заявке от ".$subject->getRequest()->getRequest()->getCreatedTime()->getDay()." ".RussianTextUtils::getMonthInGenitiveCase($subject->getRequest()->getRequest()->getCreatedTime()->getMonth())." на ".number_format($subject->getRequest()->getRequest()->getSumm(), 0, '.', ' ')."руб. назначена встреча, подробнее: {$link}");
            }
        }

        return $mav;
    }
    
    protected function daoMethod()
    {
        return $this->returnStatus ? 'income' : parent::daoMethod();
    }

    public function setForm(Form $form)
    {
        $form->
            add(Primitive::boolean('accept'))->
            add(Primitive::boolean('reject'))->
            drop('request')->
            drop('status')->
            drop('createdTime')->
            drop('summ')->
            drop('minPeriod')->
            drop('maxPeriod')->
            drop('percents')->
            drop('percentsOnly')->
                
            get('address')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars())->
                required();
        
        $form->
            get('text')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars())->
                required();
        
        $form->get('date')->required();
        $form->get('time')->required();
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id')->getStatus()->getId() == CreditRequestCreditorOfferStatus::TYPE_ACCEPTED &&
            SecurityManager::isAllowedAction(AclAction::PUBLISH_ACTION, AclContext::CREDIT_REQUEST_ID);
    }

}