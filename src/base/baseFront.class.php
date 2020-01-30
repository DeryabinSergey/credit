<?php

abstract class baseFront implements Controller
{    
    use ViewInterface, ReturnInterface;
    
    private $filterKeys = array();
    private $filter = null;
    
    protected $model = null;
    protected $view = '';
    
    /**
     * @var Form
     */
    protected $form = null;

    public function __construct()
    {
        $this->model = Model::create();
        $this->form = 
            Form::create()->
                add(Primitive::integer('page')->setMin(1)->setDefault(1));
        
        $this->addFilterKey('page');
    }

    /**
     * Вернет модель базового контроллера
     *
     * @param HttpRequest $request
     * @return Model
     */
    public function getModel(HttpRequest $request)
    {
        if ($this->isDisplayView()) {

            $this->
                model->
                    set('form', $this->form)->
                    set('curl', $this->getEncodedCurrentUrl($request))->
                    set('filter', $this->getFilter());
        }

        return $this->model;
    }

    /**
     *
     * @param HttpRequest $request
     * @return baseFront
     */
    public function initForm(HttpRequest $request)
    {
        $this->
            form->
                import($request->getGet())->
                importMore($request->getPost());
        
        foreach($this->filterKeys as $key) {
            $this->form->markGood($key);
        }        

        return $this;
    }
    
    public function initVars() { return $this; }            

    /**
     * @param HttpRequest $request
     * @return ModelAndView
     */
    public function handleRequest(HttpRequest $request)
    {
        $mav = ModelAndView::create();
        
        $this->
            initForm($request)->
            initVars()->
            checkRedirect($request);
        
        if ($this->isDisplayView()) {                
            if (!method_exists($this, 'checkPermissions') || $this->checkPermissions()) {                
                $mav->setModel($this->getModel($request));
            } else {
                $this->errorView(HttpStatus::CODE_403);
            }
        }

        return $mav->setView($this->view);
    }
    
    /**
     * Проверка на необходимость редиректа. Например для справочника - это собственный домен справочника привязанный к городу.
     * На правильную ссылку на объявление, тему на форуме и т. д.
     * @param HttpRequest $request
     * @return \baseCoreFront
     */
    protected function checkRedirect(HttpRequest $request) { return $this; }
    
    /**
     * Определение параметров редиректа
     */
    protected function getRedirectParams() { return array(); }
    
    /**
     * Добавить элемент формы по ключу в фильтр для отображения списка
     * @param string $key - ключ для фильтра
     * @return baseCoreFront
     * @throws WrongArgumentException
     */
    protected function addFilterKey($key)
    {
        Assert::isTrue($this->form->exists($key), "нельзя добавить несуществующий в форме элемент «{$key}» в фильтр");
        Assert::isFalse(isset($this->filterKeys[$key]), "элемент «{$key}» уже есть в фильтре");
        
        $this->filterKeys[$key] = $key;
        $this->filter = null;
        
        return $this;
    }
    
    /**
     * Добавить в форму элемент для фильтра
     * @param BasePrimitive $primitive
     * @return baseCoreFront
     * @throws WrongArgumentException
     */
    protected function addFilter(BasePrimitive $primitive)
    {
        $this->form->add($primitive);
        $this->addFilterKey($primitive->getName());
        
        return $this;
    }
    
    /**
     * Удалить элемент формы по ключу из фильтра для отображения списка
     * @param string $key - ключ фильтра для удаления
     * @return baseCoreFront
     * @throws WrongArgumentException
     */
    protected function removeFilterKey($key)
    {
        Assert::isTrue(isset($this->filterKeys[$key]), "элемента «{$key}» нет в фильтре");
        
        unset($this->filterKeys[$key]);
        $this->filter = null;
        
        return $this;
    }
    
    /**
     * Проверка на присутсвие ключа в фильтре
     * @param string $key - ключ для проверки
     * @return boolean
     */
    protected function isFilterKeyExists($key)
    {
        return isset($this->filterKeys[$key]);
    }
    
    /**
     * Получить фильтр для применения при отображения списка на основе ранее заданных ключей
     * @return array
     */
    protected function getFilter()
    {
        if (is_null($this->filter)) {
        
            $filter = array();

            foreach($this->filterKeys as $key) {
                if (
                    $this->form->get($key) instanceof PrimitiveIdentifierList ||
                    $this->form->get($key) instanceof PrimitiveIdentifier ||
                    $this->form->get($key) instanceof PrimitiveEnumeration ||
                    $this->form->get($key) instanceof PrimitiveEnumerationList
                ) {
                    $filter[$key] = $this->form->exportValue($key);
                } elseif ($this->form->get($key) instanceof PrimitiveBoolean) {
                    if ($this->form->getValue($key)) {
                        $filter[$key] = 1;
                    }
                } else {
                    $filter[$key] = $this->form->getValueOrDefault($key);
                }
            }
            
            $this->filter = $filter;
        }
        
        return $this->filter;
    }

    protected function getQueryResult(SelectQuery $query, ProtoDAO $dao, $cache = Cache::EXPIRES_MAXIMUM)
    {
        $result = QueryResult::create();
        $list = array();

        $count = $this->getCountByQuery($query, $dao, $cache);
        if ($count) {
            try {
                $ids = $dao->getCustomList($query, $cache);
                $ids = ArrayUtils::convertToPlainList($ids, 'id');
                $list = $dao->getListByIds($ids);
            } catch (ObjectNotFoundException $e) {}
        }

        return $result->
            setQuery($query)->
            setCount($count)->
            setList($list);
    }

    /**
     * @param SelectQuery
     * @param ProtoDAO
     * @return integer
     */
    protected function getCountByQuery(SelectQuery $query)
    {
        $countQuery = clone $query;
        
        $countQuery->
            unDistinct()->
            dropFields()->
            dropOrder()->
            limit(null, null)->
            get(
                SQLFunction::create('count', SQLFunction::create('distinct', DbField::create('id', $countQuery->getFirstTable())))->
                    setAlias('count_list')
            );

        return current(DBPool::me()->getLink()->queryRow($countQuery));
    }
}

?>