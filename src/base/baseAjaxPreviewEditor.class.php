<?php

abstract class baseAjaxPreviewEditor extends baseAjax
{
    /**
     * Объект для которого загружается изображение
     * @var BasePreviewPicture
     */
    protected $object = null;

    protected $isDifferent = false;

    public function  __construct()
    {
        parent::__construct();

        $this->form->
            add(
                Primitive::choice('type')->
                    setList(array(
                        PictureUtils::PREVIEW_IMAGE_TYPE_SMALL => PictureUtils::PREVIEW_IMAGE_TYPE_SMALL,
                        PictureUtils::PREVIEW_IMAGE_TYPE_MEDIUM => PictureUtils::PREVIEW_IMAGE_TYPE_MEDIUM,
                        PictureUtils::PREVIEW_IMAGE_TYPE_BIG => PictureUtils::PREVIEW_IMAGE_TYPE_BIG
                    ))->
                    setDefault(PictureUtils::PREVIEW_IMAGE_TYPE_SMALL)->
                    optional()
            )->
            add(
                Primitive::choice('object')->
                    required()->
                    setList(array(
                        'News'              => 'News'
                    ))
            )->
            add(Primitive::integer('id')->setMin(1)->optional())->

            addWrongLabel('type', 'тип привью не указан или указан неверно')->

            addWrongLabel('id', 'получены неверные данные')->
            addMissingLabel('id', 'получены неверные данные')->
            addCustomLabel('id', self::INTERNAL_ERROR, 'не получилось выполнить операцию с изображением');
    }
    
    protected function initVars(HttpRequest $reques)
    {
        $result = parent::initVars($reques);
        
        if (!$this->form->getErrors() && $this->form->getValue('id')) {
            $dao = ClassUtils::callStaticMethod("{$this->form->getChoiceValue('object')}::dao");
            try {
                $this->object = $dao->getById($this->form->getValue('id'));
            } catch(ObjectNotFoundException $e) {
                $this->form->markWrong('id');
            }
        }
            
        $this->isDifferent = !ClassUtils::callStaticMethod("{$this->form->getChoiceValue('object')}::create") instanceof BaseSamePreviewPicture;
            
        /**
         * Проверка на установку типа изображения.
         * Если у объекта разные привью для разных размеров - тип должен быть установлен
         */
        if (!$this->form->getErrors() && $this->isDifferent && !$this->form->getChoiceValue('type')) {
            $this->form->markWrong('type');
        }

        return $result;
    }
    
    protected function checkPermissions()
    {
	$permissions = false;
	
	if ($this->object instanceof Identifiable) {
	    if ($this->object instanceof News) {
		$permissions = SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::NEWS_ID);
	    }
	} else {
	    if ($this->form->getChoiceValue('object') == 'News') {
		$permissions = SecurityManager::isAllowedAction(AclAction::ADD_ACTION, AclContext::NEWS_ID);
	    }
	}
        
	return $permissions;
    }
}