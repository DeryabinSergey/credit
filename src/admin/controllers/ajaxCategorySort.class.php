<?php

class ajaxCategorySort extends baseAjax
{    
    public function  __construct() 
    {
        parent::__construct();
        
        $this->
            form->
                add(Primitive::identifierlist('ids')->of('Category')->required())->
                
                addMissingLabel('ids', 'список категорий не передан')->
                addWrongLabel('ids', 'передан неверный список категорий');
    }
    
    public function getModel(HttpRequest $request)
    {        
        $model = parent::getModel($request);
        $form = $this->form;
        
        try {
            $id = 1;
            foreach($form->getValue('ids') as $category) {
                $category->dao()->save($category->setSort($id));
                $id++;
            }
        } catch(Exception $e) { $form->markWrong('ids'); }
        
        if (!$form->getErrors()) {
            $model->set('success', true);
        }
        
        return $model;
    }
    
    protected function checkPermissions()
    {
        return SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::CATEGORY_ID);
    }
}