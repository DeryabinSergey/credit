<?php

class baseInvestorOfferEditor extends CommandLogger implements UserController
{    
    const ACTION_PUBLISH    = 'publish';
    
    /**
     * @return investorOfferEditor
     */
    public static function create() { return new self; }

    public function __construct()
    {
        $this->insertCommand(self::ACTION_ADD, InvestorOfferAddCommand::create());
        $this->insertCommand(self::ACTION_DELETE, InvestorOfferDropCommand::create());
        $this->insertCommand(self::ACTION_RESTORE, InvestorOfferRestoreCommand::create());
        $this->insertCommand(self::ACTION_PUBLISH, InvestorOfferPublishCommand::create());
        //$this->insertCommand(self::ACTION_UPDATE, AclContextUpdateCommand::create());

        $this->defaultAction = self::ACTION_ADD;

        parent::__construct(InvestorOffer::create());
    }

    /**
     * @param HttpRequest $request
     * @return ModelAndView
     */
    public function handleRequest(HttpRequest $request)
    {
        $mav = parent::handleRequest($request);

        if ($this->isDisplayView($mav)) {
            Singleton::getInstance('HTMLMetaManager')->setTitle('Редактор предложений инвестирования - Панель Управления');
            
            if (in_array($this->getForm()->{$this->getActionMethod()}('action'), array(self::ACTION_ADD, self::ACTION_UPDATE, self::ACTION_PUBLISH))) {
                Singleton::getInstance('HTMLMetaManager')->
                    appendJavaScript('/i/jquery.mask.min.js')->                        
                    appendJavaScript('/i/investor-offer-editor.js');
            }            
        }

        return $mav;
    }

    protected function getDefaultReturnUrl()
    {
        return CommonUtils::makeFrontUrl('investorOfferList');
    }
}