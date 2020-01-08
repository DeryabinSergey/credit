<?

class CreditorUpdateCommand extends SaveCommand implements SecurityCommand
{
    const ERROR_INTERNAL = 0x0004;
    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();

        if (!$process) {
            $list = array();
            foreach($form->getValue('id')->getCategories()->getList() as $category) {
                $list[] = $category->getCategory()->getId();
            }
            $form->get('category')->importValue($list);
        }

        try {
            $tr = InnerTransaction::begin($subject->dao());
            $mav = parent::run($subject, $form, $request);
            if ($mav->getView() == BaseEditor::COMMAND_SUCCEEDED) {
                $newList = $form->getValue('category');
                $list = $subject->getCategories()->getList();
                foreach($list as $key => $item) {
                    $drop = true;
                    foreach($newList as $newKey => $newItem) {
                        if ($newItem->getId() == $item->getCategory()->getId()) {
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
                    $list[] = CreditorCategory::create()->setCategory($newItem)->setCreditor($subject);
                }
                $subject->getCategories()->setList($list)->save();
            }
            $tr->commit();
        } catch(Exception $e) {
            $tr->rollback();
            $form->markCustom('id', self::ERROR_INTERNAL);
            error_log("Ошибка при сохранении компании кредитора: {$e->getMessage()} \n\n{$e->getTraceAsString()}");
        }

        if ($mav->getView() != BaseEditor::COMMAND_SUCCEEDED) {

            $categoryList = 
                Criteria::create(Category::dao())->
                    addOrder(OrderBy::create('sort')->asc())->
                    getList();

            $mav->
                getModel()->
                    set('categoryList', $categoryList);
        }

        return $mav;
    }

    public function setForm(Form $form)
    {
        $form->
            get('name')->
                addImportFilter(Filter::textImport())->
                addDisplayFilter(Filter::htmlSpecialChars());
        
        $form->
            add(Primitive::identifierlist('category')->of('Category'))->
            drop('categories')->
            drop('createdTime')->
            drop('user')->
            drop('active')->
            drop('deleted');
        
        if ($form->getValue('id')->isActive() || !SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::CREDITOR_ID)) {
            $form->drop('type');
        }
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof Creditor &&
            $form->getValue('id')->checkPermissions(AclAction::EDIT_ACTION);
    }
}