<?

class CreditorAddCommand extends AddCommand implements SecurityCommand
{
    const ERROR_INTERNAL    = 0x0004;
    
    public static function create() { return new self; }

    public function run(Prototyped $subject, Form $form, HttpRequest $request)
    {
        $process = $request->getServerVar('REQUEST_METHOD') == 'POST';
        $mav = ModelAndView::create();
        
        $subject->
            setUser(SecurityManager::getUser())->
            setActive(!Constants::CREDITOR_PREMODERATION);

        if ($process) {
            try {
                $tr = InnerTransaction::begin($subject->dao());
                $mav = parent::run($subject, $form, $request);
                if ($mav->getView() == BaseEditor::COMMAND_SUCCEEDED) {
                    $list = array();
                    foreach($form->getValue('category') as $category) {
                        $list[] = CreditorCategory::create()->setCreditor($subject)->setCategory($category);
                    }
                    if ($list) {
                        $subject->getCategories()->fetch()->setList($list)->save();
                    }
                }
                $tr->commit();
            } catch(Exception $e) {
                $tr->rollback();
                $form->markCustom('id', self::ERROR_INTERNAL);
                error_log("Ошибка при добавлении кредитора: {$e->getMessage()} \n\n{$e->getTraceAsString()}");
            }
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
        
        return $this;
    }

    public function checkPermissions(Form $form)
    {
        return SecurityManager::isAuth();
    }

}