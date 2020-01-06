<?php

class AclRightDropCommand extends DropCommand implements SecurityCommand
{
    const ERROR_EXTERNAL = 0x0003;
    const ERROR_INTERNAL = 0x0004;

    /**
     * @return AclRightDropCommand
     */
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $mav = ModelAndView::create();
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        
        if ($process && $form->getValue('ok')) {

            $criteria = Criteria::create(AclGroupRight::dao())->
                addProjection(Projection::property('id'))->
                add(Expression::eq('right', $form->getValue('id')->getId()));

            try {
                $tr = InnerTransaction::begin($subject->dao());

                $ids = ArrayUtils::convertToPlainList($criteria->getCustomList(), 'id');
                if ($ids) {
                    $criteria->getDao()->dropByIds($ids);
                }
                $mav = parent::run($subject, $form, $request);
                
                $tr->commit();
            } catch(DatabaseException $e) {
                $tr->rollback();
                error_log("Ошибка при удалении права, привязанного к группам: {$e->getMessage()} \n\n{$e->getTraceAsString()}");
                $form->markCustom('id', self::ERROR_INTERNAL);
            }                


        }
        
        if ($mav->getView() != BaseEditor::COMMAND_SUCCEEDED) {
            
            $groupList = array();
            $criteria = Criteria::create(AclGroupRight::dao())->
                addProjection(Projection::property('group'))->
                add(Expression::eq('right', $form->getValue('id')->getId()));
            
            try {
                $ids = ArrayUtils::convertToPlainList($criteria->getCustomList(), 'group_id');
                $groupList = AclGroup::dao()->getListByIds($ids);
            } catch(ObjectNotFoundException $e) { }
            
            $mav->
                getModel()->
                    set('groupList', $groupList);
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->add(Primitive::boolean('ok'));
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::DELETE_ACTION, AclContext::ACL_ID);
    }
}