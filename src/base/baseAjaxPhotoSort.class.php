<?php
/**
 * Сохранение сортировки изображений типа ImageOwner для объекта владельца изображений типа ImageOwner.
 * Может использоваться в проекте по адресу:
 * - /ajax/photo-sort.json
 */
class baseAjaxPhotoSort extends baseAjaxPhotoEditor
{
    protected $images = null;
    
    public function  __construct() 
    {
        parent::__construct();

        $primitiveList = new PrimitiveArray('ids');
        
        $this->
            form->
                add($primitiveList->required())->

                addWrongLabel('ids', 'получены неверные данные')->
                addMissingLabel('ids', 'получены неверные данные');
    }
    
    protected function initVars(HttpRequest $request)
    {
        $result = parent::initVars($request);
        
        if (!$this->form->getErrors()) {
            $list = $this->form->getValue('ids');
            foreach($list as $id) {
                if (!is_numeric($id) || $id != intval($id)) {
                    $this->form->markWrong('ids');
                    break;
                }
            }
            
            if (!$this->form->getErrors()) {
                $dao = ClassUtils::callStaticMethod("{$this->form->getChoiceValue('object')}::dao");
                try {
                    $this->images = $dao->getListByIds($list);
                    
                    $imageOwners = $imageUsers = array();
                    foreach($this->images as $image) {
                        $imageOwners[] = $image->getOwner() instanceof ImageOwner ? $image->getOwner()->getId() : null;
                        $imageUsers[] = $image->getUser() instanceof User ? $image->getUser()->getId() : null;
                    }
                    
                    $imageOwners = array_unique($imageOwners);
                    $imageUsers = array_unique($imageUsers);
                    
                    if (count($imageOwners) > 1 || (count($imageOwners) == 1 && current($imageOwners) === null && count($imageUsers) > 1)) {
                        $this->form->markWrong('ids');
                    }
                } catch(ObjectNotFoundException $e) {
                    $this->form->markWrong('ids');
                }
            }
        }
        
        return $result;
    }
    
    public function getModel(HttpRequest $request)
    {
        $model = parent::getModel($request);
        $form = $this->form;
        
        try {
            $tr = InnerTransaction::begin(ClassUtils::callStaticMethod("{$this->form->getValue('object')}::dao"));
            $i = 1;
            foreach($this->images as $image) {
                $image = $image->dao()->save($image->setSort($i++));
            }

            $model = $model->set('success', true);
            $tr->commit();
        } catch(Exception $e) {
            $tr->rollback();
            error_log("Ошибка при сохранении сортировки привязанных изображений: {$e->getMessage()}\n{$e->getTraceAsString()}");
            $form->markCustom('errorFlag', self::INTERNAL_ERROR);
        } 

        return $model;
    }

    protected function checkPermissions()
    {
        $perm = true;
        
        foreach($this->images as $image) {
            if (($perm = $image->checkPermissions(AclAction::EDIT_ACTION)) == false) {
                break;
            }
        }
            
        return parent::checkPermissions() && $perm;
    }
}