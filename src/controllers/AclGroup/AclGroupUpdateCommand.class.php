<?

class AclGroupUpdateCommand extends SaveCommand implements SecurityCommand
{
    const ERROR_DUPLICATE = 0x0003;
    const ERROR_INTERNAL = 0x0004;
    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();

        if (!$process) {
            $list = array();
            foreach($form->getValue('id')->getRights()->getList() as $groupRight) {
                $list[$groupRight->getRight()->getId()] = $groupRight->getRight()->getId();
            }
            $form->get('right')->importValue($list);
        }

        try {
            $tr = InnerTransaction::begin($subject->dao());
            $mav = parent::run($subject, $form, $request);
            if ($mav->getView() == BaseEditor::COMMAND_SUCCEEDED) {
                $newList = $form->getValue('right');
                $list = $subject->getRights()->getList();
                foreach($list as $key => $item) {
                    $drop = true;
                    foreach($newList as $newKey => $newItem) {
                        if ($newItem->getId() == $item->getRight()->getId()) {
                            $drop = false;
                            unset($newList[$newKey]);
                            break;
                        }
                    }
                    if ($drop) {
                        unset($list[$key]);
                    }
                }
                foreach($newList as $newItem) {
                    $list[] = AclGroupRight::create()->setRight($newItem)->setGroup($subject);
                }
                $subject->getRights()->setList($list)->save();
            }
            $tr->commit();
        } catch(DuplicateObjectException $e) {
            $tr->rollback();
            $form->markCustom('name', self::ERROR_DUPLICATE);
        } catch(Exception $e) {
            $tr->rollback();
            $form->markCustom('name', self::ERROR_INTERNAL);
            error_log("Ошибка при сохранении группы пользователей: {$e->getMessage()} \n\n{$e->getTraceAsString()}");
        }

        if ($mav->getView() != BaseEditor::COMMAND_SUCCEEDED) {
            $rightList = array();
            $criteria = Criteria::create(AclRight::dao())->addOrder(OrderBy::create('context.name')->asc());
            foreach($criteria->getList() as $right) {
                if (isset($rightList[$right->getContext()->getId()])) {
                    $rightList[$right->getContext()->getId()][] = $right;
                } else {
                    $rightList[$right->getContext()->getId()] = array($right);
                }
            }

            $mav->
                getModel()->
                    set('rightList', $rightList)->
                    set('process', $process);
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            get('name')->
                addImportFilter(Filter::textImport())->
                addImportFilter(Filter::htmlSpecialChars());
        
        $form->
            add(Primitive::identifierlist('right')->of('AclRight'))->
            drop('rights');
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::ACL_ID);
    }
}