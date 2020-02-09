<?php

abstract class baseAjax implements Controller
{
    const FORBIDDEN         = 0x0003;
    const INTERNAL_ERROR    = 0x0004;
    const NOT_FOUND         = 0x0005;
    const NEED_AUTH         = 0x0006;
    
    const SESSION_SECURITY_VAR  = 'security-code';
    
    /**
     * Безопасный контроллер - по умолчанию включена csfr защита
     * @var Boolean
     */
    protected $secureController = false;
    protected $securityCode = null;
    
    /**
     * @var Form
     */
    protected $form;
    
    /**
     * @var JsonView
     */
    protected $view;
    
    public function  __construct()
    {
        $this->form = Form::create();
        $this->view = JsonView::create();
        
        $this->
            form->
                add(Primitive::boolean('errorFlag'))->
                
                addCustomLabel('errorFlag', self::INTERNAL_ERROR, 'ошибка на сервере')->
                addCustomLabel('errorFlag', self::NEED_AUTH, 'необходима авторизация')->
                addCustomLabel('errorFlag', self::FORBIDDEN, 'нет доступа');
        
        if ($this->secureController) {
            $this->
                form->
                    add(Primitive::string('securityCode')->required())->
                    addWrongLabel('securityCode', 'нет доступа')->
                    addMissingLabel('securityCode', 'нет доступа');
        }
        
        return $this;
    }

    /**
     * @param HttpRequest $request
     * @return Model
     */
    public function getModel(HttpRequest $request)
    {
        return Model::create();
    }

    public function handleRequest(HttpRequest $request)
    {
        $mav = ModelAndView::create();
        
        if ($this instanceof UserController && !SecurityManager::isAuth()) {
            $this->form->markCustom('errorFlag', self::NEED_AUTH);
        } else {
            $this->
                initForm($request)->
                initVars($request);
            
            if (!$this->form->getErrors()) {
                if ($this->checkPermissions()) {
                    $model = $this->getModel($request);
                } else {
                    $this->form->markCustom('errorFlag', self::FORBIDDEN);
                }
            }
        }
        
        if ($this->form->getErrors()) {
            $model = Model::create()->set('errors', $this->form->getTextualErrors());
        }

        return $mav->setModel($model)->setView($this->view);
    }
    
    protected function initForm(HttpRequest $request)
    {
        $this->
            form->
                import($request->getPost())->
                importMore($request->getGet());
        
        return $this;
    }
    
    protected function initVars(HttpRequest $request)
    {
        if ($this->secureController) {

            $codes = array();
            foreach(($request->hasSessionVar(self::SESSION_SECURITY_VAR) ? $request->getSessionVar(self::SESSION_SECURITY_VAR) : array()) as $editor => $names) {
                foreach($names as $code) {
                    $this->securityCode = is_array($this->securityCode) ? array_merge($this->securityCode, array_values($code)) : array_values($code);
                }
            }
            
            if (is_array($this->securityCode)) { $this->securityCode = array_unique($this->securityCode); }
        }
        
        return $this;
    }
    
    protected function checkPermissions()
    {
        return !$this->secureController || in_array($this->form->getValue('securityCode'), $this->securityCode);
    }
}