<?php

class baseAjaxPreviewDelete extends baseAjaxPreviewEditor
{
    public function getModel(HttpRequest $request)
    {
        $model = parent::getModel($request);
        $form = $this->form;

        if ($this->object instanceof Identifiable) {
            $previewFiles = array();
            /**
             * Если привьюшки разные - надо взять только одну
             */
            if ($this->isDifferent) {
                $previewFiles = array($this->object->getPreviewPath($form->getChoiceValue('type')));
                if ($form->getChoiceValue('type') == PictureUtils::PREVIEW_IMAGE_TYPE_SMALL) {
                    $this->object->dropPreview();
                } elseif ($form->getChoiceValue('type') == PictureUtils::PREVIEW_IMAGE_TYPE_MEDIUM) {
                    $this->object->dropPreviewMedium();
                } elseif ($form->getChoiceValue('type') == PictureUtils::PREVIEW_IMAGE_TYPE_BIG) {
                    $this->object->dropPreviewBig();
                } else {
                    $previewFiles = array();
                }
            } else {
                if ($this->object->getPreview()) {
                    $previewFiles = PictureUtils::getPreviewFiles($this->object);
                }
                $this->object->dropPreview();
            }

            try {
                $tr = InnerTransaction::begin($this->object->dao());
                $this->object = $this->object->dao()->save($this->object);
                foreach($previewFiles as $file) {
                    if (file_exists($file)) unlink($file);
                }
                $tr->commit();

                $model->set('success', true);
            } catch(Exception $e) {
                error_log("Ошибка Базы Данных при удалении файла привью: {$e->getMessage()}\n{$e->getTraceAsString()}");
                $tr->rollback();

                $this->form->markCustom('errorFlag', self::INTERNAL_ERROR);
            }
	} else {
            $sessionVar = 'preview-'.($this->isDifferent?$form->getChoiceValue('type').'-':'').$form->getChoiceValue('object');
            $previewFile = Session::exist($sessionVar) ? Session::get($sessionVar) : false;

            try {
                if ($previewFile) {
                    if (file_exists(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL.$previewFile)) unlink(UPLOAD_PATH.IMAGE_PATH_TEMP_ORIGINAL.$previewFile);
                    if (file_exists(UPLOAD_PATH.IMAGE_PATH_TEMP.$previewFile)) unlink(UPLOAD_PATH.IMAGE_PATH_TEMP.$previewFile);
                    Session::drop($sessionVar);
                }
                $model->set('success', true);
            } catch(Exception $e) {
                error_log("Не удалось удалить временные файлы привью {$e->getMessage()} :\r\n{$e->getTraceAsString()}");
                $this->form->markCustom('errorFlag', self::INTERNAL_ERROR);
            }
        }
            
        return $model;
    }
}