<?php

class baseAjaxPreviewUpload extends baseAjaxPreviewEditor
{
    const ERROR_SMALL   = 0x0006;

    protected $biggest = array(0, 0);

    /**
     * Массив объектов для типов привью которых необходимо при загрузке делать crop
     * @var array
     */
    private $cropSizes = array(
        'News' => array(
            PictureUtils::PREVIEW_IMAGE_TYPE_SMALL => PictureUtils::PREVIEW_IMAGE_TYPE_SMALL,
            PictureUtils::PREVIEW_IMAGE_TYPE_MEDIUM => PictureUtils::PREVIEW_IMAGE_TYPE_MEDIUM,
            PictureUtils::PREVIEW_IMAGE_TYPE_BIG => PictureUtils::PREVIEW_IMAGE_TYPE_BIG
        )
    );
    
    public function  __construct()
    {   
        parent::__construct();
    
        $this->form->
            add(Primitive::image('file')->setAllowedMimeTypes(PictureUtils::getImageMimeTypeList())->required())->
        
            addMissingLabel('file', 'не выбран файл')->
            addWrongLabel('file', 'файл не является изображением');
    }
        
    protected function initForm(HttpRequest $request)
    {   
        $result = parent::initForm($request);
    
        $this->
            form->
                importMore($request->getFiles());
    
        return $this;
    }

    protected function initVars(HttpRequest $request)
    {   
        $result = parent::initVars($request);
 
        /**
         * Проверка на наличие доступных размеров привью
         * Если привьюшки одинаковые для всех, то смотреть по максимальной, если разные - то для конкретного размера
         */
        if (!$this->form->getErrors()) {
 
            if ($this->isDifferent) {
                $types = array($this->form->getChoiceValue('type'));
            } else {
                $types = PictureUtils::getAvailablePreviewTypes(ClassUtils::callStaticMethod("{$this->form->getChoiceValue('object')}::create"));
            } 

            foreach($types as $type) {
                $sizes = PictureUtils::getPreviewResizeSizes(ClassUtils::callStaticMethod("{$this->form->getChoiceValue('object')}::create"), $type);
                if ($sizes[0] > $this->biggest[0] || $sizes[1] > $this->biggest[1]) {
                    $this->biggest = $sizes;
                }
                if ($this->form->get('file')->getWidth() < $sizes[0] && $this->form->get('file')->getHeight() < $sizes[1]) {
                    $this->form->markCustom('file', self::ERROR_SMALL);
                }
            }
            $this->form->addCustomLabel('file', self::ERROR_SMALL, "картинка слишком маленькая, минимальный размер {$this->biggest[0]}x{$this->biggest[1]} px");
            
        }
            
        return $result;
    }
    
    public function getModel(HttpRequest $request)
    {
        $model = parent::getModel($request);
        $form = $this->form;

        if ($this->object instanceof Identifiable) {

            $previewFiles = array();
            $previewImageType = $form->get('file')->getType();

            if ($this->isDifferent) {

                $previewType = $form->getChoiceValue('type');

                switch($form->getChoiceValue('type')) {
                    case PictureUtils::PREVIEW_IMAGE_TYPE_SMALL:
                        if ($this->object->getPreview()) {
                            $previewFiles = array($this->object->getPreviewPath($previewType));
                        }
                        $this->object->setPreview($previewImageType);
                        break;

                    case PictureUtils::PREVIEW_IMAGE_TYPE_MEDIUM:
                        if ($this->object->getPreviewMedium()) {
                            $previewFiles = array($this->object->getPreviewPath($previewType));
                        }
                        $this->object->setPreviewMedium($previewImageType);
                        break;

                    case PictureUtils::PREVIEW_IMAGE_TYPE_BIG:
                        if ($this->object->getPreviewBig()) {
                            $previewFiles = array($this->object->getPreviewPath($previewType));
                        }
                        $this->object->setPreviewBig($previewImageType);
                        break;
                }

            } else {
                if ($this->object->getPreview()) {
                    $previewFiles = PictureUtils::getPreviewFiles($this->object);
                }

                $this->object->setPreview($previewImageType);
            }
	    
	    try {
                $tr = InnerTransaction::begin($this->object->dao());
                $this->object = $this->object->dao()->save($this->object);
                foreach($previewFiles as $file) {
                    if (file_exists($file)) unlink($file);
                }
                if ($this->isDifferent) {
                    $list = array($form->getChoiceValue('type'));
                } else {
                    $list = PictureUtils::getAvailablePreviewTypes($this->object);
                }
                foreach ($list as $type) {
                    if (PictureUtils::checkDir($this->object->getPreviewPath($type)) !== true) {
                        throw new Exception("Can`t create preview directory {$this->object->getPreviewPath($type)}");
                    }
                    PictureUtils::resize($form->getValue('file'), PictureUtils::getPreviewResizeSizes($this->object, $type), $this->object->getPreviewPath($type),  $this->isCrop($form->getValue('object'), $type));
                }
                $tr->commit();
                return $model->set('file', $this->object->getPreviewUrl($form->getValue('type')).'?'.time());
            } catch(Exception $e) {
                error_log("Ошибка Базы Данных при добавлении привью {$e->getMessage()} :\r\n{$e->getTraceAsString()}");
                $tr->rollback();
                $form->markCustom('errorFlag', self::INTERNAL_ERROR);
            }
	} else {
            $preview = ViewTextUtils::createRandomPassword().'.'.$form->get('file')->getType()->getExtension();
            $previewOld = Session::exist('preview-'.($this->isDifferent?$form->getChoiceValue('type').'-':'').$form->getChoiceValue('object')) ? Session::get('preview-'.($this->isDifferent?$form->getChoiceValue('type').'-':'').$form->getChoiceValue('object')) : false;

            if (
                ($this->isDifferent && PictureUtils::checkDir(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL)) ||
                (!$this->isDifferent && PictureUtils::checkDir(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL) && PictureUtils::checkDir(UPLOAD_PATH.IMAGE_PATH_TEMP))
            ) {
                try {
                    if ($this->isDifferent) {
                        PictureUtils::resize($form->getValue('file'), PictureUtils::getPreviewResizeSizes(ClassUtils::callStaticMethod("{$form->getChoiceValue('object')}::create"), $form->getChoiceValue('type')), UPLOAD_PATH.IMAGE_PATH_TEMP.$preview, $this->isCrop($form->getChoiceValue('object'), $form->getChoiceValue('type')));
                    } else {
                        PictureUtils::resize($form->getValue('file'), $this->biggest[0] > $this->biggest[1] ? $this->biggest[0] : $this->biggest[1], UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL.$preview, false, false);
                        PictureUtils::resize($form->getValue('file'), PictureUtils::getPreviewResizeSizes(ClassUtils::callStaticMethod("{$form->getChoiceValue('object')}::create"), $form->getActualChoiceValue('type')), UPLOAD_PATH.IMAGE_PATH_TEMP.$preview, $this->isCrop($form->getChoiceValue('object'), $form->getActualChoiceValue('type')));
                    }

                    if ($previewOld) {
                        if (file_exists(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL.$previewOld)) { unlink(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL.$previewOld); }
                        if (file_exists(UPLOAD_PATH.IMAGE_PATH_TEMP.$previewOld)) { unlink(UPLOAD_PATH.IMAGE_PATH_TEMP.$previewOld); }
                    }

                    Session::assign('preview-'.($this->isDifferent?$form->getChoiceValue('type').'-':'').$form->getChoiceValue('object'), $preview);

                    $model->
                        set('file', UPLOAD_URL.IMAGE_PATH_TEMP.$preview.'?'.time());
                } catch (Exception $e) {
                    error_log("Не удалось записать временные файлы привью {$e->getMessage()} :\r\n{$e->getTraceAsString()}");
                    $form->markCustom('errorFlag', self::INTERNAL_ERROR);
                }
            } else {
                error_log('Не удалось создать временную директорию для сохранения привью');
                $form->markCustom('errorFlag', self::INTERNAL_ERROR);
            }
        }
            
        return $model;
    }
    
    /**
     * Проверка на необходимость обрезания картинки привью для объекта в зависимости от типа (размера)
     * @param type $object - объект, владелец привью
     * @param type $type - тип привью для которого делается проверка
     * @return boolean
     */
    private function isCrop($object, $type)
    {
        return isset($this->cropSizes[$object]) && isset($this->cropSizes[$object][$type]);
    }
}