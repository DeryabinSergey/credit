<?

class InvestorOfferAddCommand extends AddCommand implements SecurityCommand
{
    const ERROR_DUPLICATE = 0x0003;

    /**
     * @return InvestorOfferAddCommand
     */    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $subject->
            setUser(SecurityManager::getUser())->
            setActive(!Constants::INVESTOR_PREMODERATION);

        if ($process) {      
            
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
            
            $mav = parent::run($subject, $form, $request);
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
        
        $form->add(Primitive::string('minSumm')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", "")));
        $form->add(Primitive::string('maxSumm')->addImportFilter(Filter::pcre()->setExpression("/([^\d]+)/isu", ""))->required());
        $form->get('percents')->setMin(0.01);
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAuth();
    }
}