<?php
/**
 * Поворот изображения типа ImageBase по часовой или против часовой стрелки на 90 градусов
 * Может использоваться в проекте по адресу:
 * - /ajax/photo-rotate.json
 */

class baseAjaxPhotoRotate extends baseAjaxPhotoItemEditor
{
    public function  __construct()
    {
        parent::__construct();

        $this->form->add(Primitive::boolean('cw'));
    }
    
    public function getModel(HttpRequest $request)
    {
        $model = parent::getModel($request);
        $form = $this->form;

        try {
            $sizes = array();
            PictureUtils::rotate($this->image->getPath(), $form->getValue('cw'), null, $sizes);
            PictureUtils::rotate($this->image->getPath(true), $form->getValue('cw'));
            $this->image->dao()->save($this->image->setWidth($sizes[0])->setHeight($sizes[1]));

            $model = 
                $model->
                    set('url', $this->image->getUrl(true) . '?ts='.time().rand(0, 100))->
                    set('id', $this->image->getId());
        } catch(Exception $e) {
            error_log("Ошибка при повороте привязанного изображения {$e->getMessage()} : \r\n{$e->getTraceAsString()}");
            $form->markCustom('errorFlag', self::INTERNAL_ERROR);
        }

        return $model;
    }

    protected function checkPermissions()
    {
        return 
            parent::checkPermissions() && 
            (
                $this->image->checkPermissions(AclAction::EDIT_ACTION) ||
                (
                    $this->image->getOwner() instanceof CreditRequest &&
                    $this->image->getOwner()->getStatus()->getId() == CreditRequestStatus::TYPE_CONCIDERED && SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::CREDIT_REQUEST_ID)
                )
            );
    }
}