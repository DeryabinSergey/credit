<?

class CreditorPublishCommand extends SaveCommand implements SecurityCommand
{
    const ERROR_INTERNAL = 0x0004;
    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();

        if (!$process && $form->exists('category')) {
            $list = array();
            foreach($form->getValue('id')->getCategories()->getList() as $category) {
                $list[] = $category->getCategory()->getId();
            }
            $form->get('category')->importValue($list);
        }
        
        $form->getValue('id')->setActive(true);

        try {
            $tr = InnerTransaction::begin($subject->dao());
            $mav = parent::run($subject, $form, $request);
            if ($mav->getView() == BaseEditor::COMMAND_SUCCEEDED) {
                if ($form->exists('category')) {
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
                
                Mail::create()->
                    setTo($form->getValue('id')->getUser()->getEmail())->
                    setFrom(DEFAULT_FROM)->
                    setSubject('Уведомление о размещении')->
                    setText('Ваша компания кредитор размещена на портале.'.($form->getValue('comment')?"\r\n\r\nКомментарий администрации: {$form->getDisplayValue('comment')}":''))->
                    send();                
            }
            $tr->commit();            
        } catch(Exception $e) {
            $tr->rollback();
            $form->markCustom('id', self::ERROR_INTERNAL);
            error_log("Ошибка при публикации компании кредитора: {$e->getMessage()} \n\n{$e->getTraceAsString()}");
        }

        if (
            $mav->getView() != BaseEditor::COMMAND_SUCCEEDED &&
            $form->exists('category')
        ) {

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
            drop('categories')->
            drop('createdTime')->
            drop('user')->
            drop('active')->
            drop('deleted')->
            add(Primitive::string('comment')->addImportFilter(Filter::textImport())->addDisplayFilter(Filter::htmlSpecialChars()));
        
        if (SecurityManager::isAllowedAction(AclAction::EDIT_ACTION, AclContext::CREDITOR_ID)) {
            $form->
                add(Primitive::identifierlist('category')->of('Category'))->
                get('name')->
                    addImportFilter(Filter::textImport())->
                    addDisplayFilter(Filter::htmlSpecialChars());
        } else {
            $form->
                drop('type')->
                drop('name');
        }
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return 
            $form->getValue('id') instanceof Creditor &&
            $form->getValue('id')->checkPermissions(AclAction::PUBLISH_ACTION);
    }
}