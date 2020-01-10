<?php

abstract class baseAjax implements Controller
{
    const FORBIDDEN         = 0x0003;
    const INTERNAL_ERROR    = 0x0004;
    const NOT_FOUND         = 0x0005;
    const NEED_AUTH         = 0x0006;
    
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
                initVars();
            
            if (!$this->form->getErrors()) {
                if (method_exists($this, 'checkPermissions') && !$this->checkPermissions()) {
                    $this->form->markCustom('errorFlag', self::FORBIDDEN);
                } else {
                    $model = $this->getModel($request);
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
    
    protected function initVars() { return $this; }
}

?>