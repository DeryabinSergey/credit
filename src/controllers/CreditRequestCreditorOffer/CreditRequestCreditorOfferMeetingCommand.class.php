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
                    Mail::create()->
                        setTo($subject->getRequest()->getCreditor()->getUser()->getEmail())->
                        setFrom(DEFAULT_FROM)->
                        setSubject('Назначена встреча с заемщиком')->
                        setText("По заявке на кредит назначена встреча с заемщиком. Посмотреть заявку: ".CommonUtils::makeUrl('creditRequestEditor', array('action' => CommandContainer::ACTION_VIEW, 'id' => $subject->getRequest()->getId()), PATH_WEB_CREDITOR)."\r\n\r\nАдрес: {$form->getValue('address')}\r\n\r\nДата и время: {$form->getValue('date')->toFormatString('d.m.Y')} ".sprintf('%02d:%02d', $form->getValue('time')->getHour(), $form->getValue('time')->getMinute())."\r\n\r\nСопроводительная информация отправленная заемщику: {$form->getValue('text')}")->
                        send();
                }
                /**
                 * Отправка уведомления заемщику
                 */
                if ($subject->getRequest()->getRequest()->getUser()->getEmail()) {
                    Mail::create()->
                        setTo($subject->getRequest()->getRequest()->getUser()->getEmail())->
                        setFrom(DEFAULT_FROM)->
                        setSubject('Назначен визит в кредитную организацию')->
                        setText("По заявке на кредит назначена встреча. Посмотреть заявку: ".CommonUtils::makeUrl('creditRequestEditor', array('action' => CommandContainer::ACTION_VIEW, 'id' => $subject->getRequest()->getRequest()->getId()), PATH_WEB_BASE)."\r\n\r\nАдрес: {$form->getValue('address')}\r\n\r\nДата и время: {$form->getValue('date')->toFormatString('d.m.Y')} ".sprintf('%02d:%02d', $form->getValue('time')->getHour(), $form->getValue('time')->getMinute())."\r\n\r\nСопроводительная информация: {$form->getValue('text')}")->
                        send();
                }
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