<?php
/**
 * Загрузка изображений типа ImageOwner для объекта владельца изображений типа ImageOwner.
 * Может использоваться в проекте по адресу:
 * - /ajax/photo-upload.json
 */
class baseAjaxPhotoUpload extends baseAjaxPhotoEditor
{
    const ERROR_SMALL       = 0x0007;
    
    /**
     * @var ImageOwner
     */
    protected $imageOwner = null;
    
    protected $raw = null;


    public function  __construct() 
    {
        parent::__construct();

        $this->
            form->
                add(
                    Primitive::choice('imageOwner')->
                        required()->
                        setList(
                            array(
                                'CreditRequest'             => 'CreditRequest'
                            )
                        )
                )->
                add(Primitive::integer('id')->setMin(1)->optional())->

                addWrongLabel('imageOwner', 'значение владельца фотографий передано неверно')->
                addMissingLabel('imageOwner', 'значение владельца фотографий не передано')->

                addWrongLabel('id', 'получены неверные данные')->
                addMissingLabel('id', 'получены неверные данные')->

                add(Primitive::image('file')->setAllowedMimeTypes(PictureUtils::getImageMimeTypeList())->required())->

                addMissingLabel('file', 'не выбран файл')->
                addWrongLabel('file', 'файл не является изображением');
    }
    
    protected function initForm(HttpRequest $request)
    {
        parent::initForm($request);
        
        $this->form->importMore($request->getFiles());
        
        return $this;
    }
    
    protected function initVars(HttpRequest $request)
    {
        $result = parent::initVars($request);
        
        if (!$this->form->getErrors()) {
            if ($this->form->getValue('id')) {
                $dao = ClassUtils::callStaticMethod("{$this->form->getChoiceValue('imageOwner')}::dao");
                try {
                    $this->imageOwner = $dao->getById($this->form->getValue('id'));
                } catch(ObjectNotFoundException $e) {
                    $this->form->markWrong('id');
                }
            }
        }
        
        $raw = $this->form->get('file')->getRawValue();
        if (is_array($raw) && $raw) {
            $this->raw = $raw;
            $this->
                form->
                    addCustomLabel('errorFlag', self::INTERNAL_ERROR, 'ошибка на сервере при добавлении файла «'.$raw['name'].'»')->
                    addWrongLabel('file', 'файл «'.$raw['name'].'» не является изображением');            
        }
        
        if (!$this->form->getErrors()) {
            $sizes = PictureUtils::getImageResizeSizes(ClassUtils::callStaticMethod("{$this->form->getValue('object')}::create"), true);
            if ($this->form->get('file')->getWidth() < $sizes[0] && $this->form->get('file')->getHeight() < $sizes[1]) {
                $this->
                    form->
                        addCustomLabel('file', self::ERROR_SMALL, "размер изображения файла «{$raw['name']}» слишком маленький, минимальный {$sizes[0]}x{$sizes[1]} px")->
                        markCustom('file', self::ERROR_SMALL);
            }
        }
        
        return $result;
    }

    public function getModel(HttpRequest $request)
    {
        $model = parent::getModel($request);
        $form = $this->form;
            
        $image = ClassUtils::callStaticMethod("{$this->form->getValue('object')}::create")->
            setUser(SecurityManager::getUser())->
            setType($form->get('file')->getType());
        if ($this->imageOwner instanceof Identifiable) {
            $image->setOwner($this->imageOwner);
        }
        if ($image instanceof ImageUniqueFileName) {
            $image->setFileName(CommonUtils::genUuid());
        }

        try {
            $tr = InnerTransaction::begin($image->dao());
            $image = $image->dao()->add($image);
            if (PictureUtils::checkDir($image->getPath()) && PictureUtils::checkDir($image->getPath(true))) {
                $sizes = array();
                PictureUtils::resize($form->getValue('file'), PictureUtils::getImageResizeSizes($image), $image->getPath(), false, true, $sizes);
                PictureUtils::resize($form->getValue('file'), PictureUtils::getImageResizeSizes($image, true), $image->getPath(true), true);
                $image->dao()->save($image->setWidth($sizes[0])->setHeight($sizes[1]));
            } else {
                throw new Exception("Can`t create image directory {$image->getPath()}");
            }
            $tr->commit();                
            $model->
                set('file', $image->getUrl(true).'?'.time())->
                set('fileFull', $image->getUrl().'?'.time())->
                set('width', $image->getWidth())->
                set('height', $image->getHeight())->
                set('name', $this->raw['name'])->
                set('id', $image->getId());
        } catch(Exception $e) {
            error_log("Ошибка Базы Данных при добавлении фото: {$e->getMessage()}\n{$e->getTraceAsString()}");
            $tr->rollback();
            $form->markCustom('errorFlag', self::INTERNAL_ERROR);
        }
        
        return $model;
    }

    protected function checkPermissions()
    {
        return 
            parent::checkPermissions() && 
            (
                !$this->imageOwner instanceof Identifiable || 
                (
                    $this->imageOwner->checkPermissions(AclAction::EDIT_ACTION) ||
                    (
                        $this->imageOwner instanceof CreditRequest && $this->imageOwner->getId() &&
                        !$this->imageOwner->isDeleted() &&
                        (
                            (
                                $this->imageOwner->getUser()->getId() == SecurityManager::getUser()->getId() &&
                                in_array($this->imageOwner->getStatus()->getId(), array(CreditRequestStatus::TYPE_INCOME, CreditRequestStatus::TYPE_CONCIDERED))
                            ) || (
                                $this->imageOwner->getStatus()->getId() == CreditRequestStatus::TYPE_CONCIDERED && SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::CREDIT_REQUEST_ID)
                            )
                        )
                    )
                )
            );
    }
}