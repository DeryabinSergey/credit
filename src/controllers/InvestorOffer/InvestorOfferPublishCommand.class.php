<?

class InvestorOfferPublishCommand extends SaveCommand implements SecurityCommand
{
    /**
     * @return InvestorOfferPublishCommand
     */    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();

        if ($process) {      
            
            if ($form->exists('percents')) {
                if ($form->getValue('minSumm') < 1) $form->get('minSumm')->dropValue ();
                if ($form->getValue('maxSumm') < 1) {
                    $form->markMissing('maxSumm')->get('maxSumm')->dropValue();
                }
                if ($form->getValue('minPeriod') < 0) $form->get('minPeriod')->dropValue();
                if ($form->getValue('maxPeriod') < 0) $form->get('maxPeriod')->dropValue();
                if (
                    ($form->getValue('minPeriod') && $form->getValue('maxPeriod') && $form->getValue('minPeriod') > $form->getValue('maxPeriod'))
                ) {
                    $val = $form->getValue('maxPeriod');
                    $form->setValue('maxPeriod', $form->getValue('minPeriod'));
                    $form->setValue('minPeriod', $val);
                }
                if (
                    ($form->getValue('minSumm') && $form->getValue('maxSumm') && $form->getValue('minSumm') > $form->getValue('maxSumm'))
                ) {
                    $val = $form->getValue('maxSumm');
                    $form->setValue('maxSumm', $form->getValue('minSumm'));
                    $form->setValue('minSumm', $val);
                }
            }
            
            $form->getValue('id')->setActive(true);
            
            $mav = parent::run($subject, $form, $request);
            
            Mail::create()->
                setTo($form->getValue('id')->getUser()->getEmail())->
                setFrom(DEFAULT_FROM)->
                setSubject('Уведомление о размещении на портале '.DEFAULT_MAILER)->
                setText('Предложение инвестирования было опубликовано.'.($form->getValue('comment')?"\r\n\r\nКомментарий администрации: {$form->getDisplayValue('comment')}":''))->
                send();
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            drop('createdTime')->
            drop('user')->
            drop('deleted')->
            drop('active')->
            drop('minSumm')->
            drop('maxSumm');
        
        if (SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::INVESTOR_OFFER_ID)) {
            $form->add(Primitive::string('minSumm')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", "")));
            $form->add(Primitive::string('maxSumm')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", ""))->required());
            $form->get('percents')->setMin(0.01);
        } else {
            $form->
                drop('minPeriod')->
                drop('maxPeriod')->
                drop('percents')->
                drop('type');
        }
        
        $form->add(Primitive::string('comment')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars()));
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof InvestorOffer &&
            !$form->getValue('id')->isActive() &&
            SecurityManager::isAllowedAction(AclAction::PUBLISH_ACTION, AclContext::INVESTOR_OFFER_ID);
    }
}