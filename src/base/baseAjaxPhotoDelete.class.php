<?php
/**
 * Удаление изображения типа ImageBase у объекта типа ImageOwner
 * Может использоваться в проекте по адресу:
 * - /ajax/photo-delete.json
 */
class baseAjaxPhotoDelete extends baseAjaxPhotoItemEditor
{
    public function getModel(HttpRequest $request)
    {
        $model = parent::getModel($request);
        
        try {
            $imageId = $this->image->getId();
            $this->image = $this->image->dao()->dropById($imageId);

            $model = $model->set('id', $imageId);
        } catch(Exception $e) {
            error_log("Ошибка при удалении привязанного изображения {$e->getMessage()} : \r\n{$e->getTraceAsString()}");
            $this->form->markCustom('errorFlag', self::INTERNAL_ERROR);
        }

        return $model;
    }

    protected function checkPermissions()
    {
        return parent::checkPermissions() && $this->image->checkPermissions(AclAction::DELETE_ACTION);
    }
}