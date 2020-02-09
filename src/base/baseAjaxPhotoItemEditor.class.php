<?php
/**
 * Базовый класс для работы с одним отдельными изображением типа ImageBase
 */
abstract class baseAjaxPhotoItemEditor extends baseAjaxPhotoEditor
{
    /**
     * @var ImageBase
     */
    protected $image = null;
    
    public function  __construct() 
    {
        parent::__construct();

        $this->
            form->
                add(Primitive::integer('id')->setMin(1)->required())->

                addWrongLabel('id', 'получены неверные данные')->
                addMissingLabel('id', 'получены неверные данные');
    }
    
    protected function initVars(HttpRequest $request)
    {
        $result = parent::initVars($request);
        
        if (!$this->form->getErrors()) {
            $dao = ClassUtils::callStaticMethod("{$this->form->getValue('object')}::dao");
            try {
                $this->image = $dao->getById($this->form->getValue('id'));
            } catch(ObjectNotFoundException $e) {
                $this->form->markWrong('id');
            }
        }
        
        return $result;
    }
}