<?php
/**
 * Родительский класс для организации команд, которые будут вызываться совместно друг с другом для некоего Prototyped объекта
 * Применяется для обособления общего кода от конкретной реализации конкретного набора команд
 *
 * @see onPHP/main/flow/EditorController.class.php
 * @see onPHP/main/flow/BaseEditor.class.php
 * @category Flow
 */
abstract class CommandContainer extends BaseEditor
{
    use ViewInterface, ReturnInterface;
    
    const ACTION_ADD        = 'add';
    const ACTION_UPDATE     = 'update';
    const ACTION_DELETE     = 'delete';
    const ACTION_RESTORE    = 'restore';

    const COMMAND_CANCELED  = 'cancel';
    
    const SESSION_SECURITY_VAR  = 'security-code';
    
    protected $process = false;
    
    protected $mapUnsecureCommands = array();
    /**
     * Безопасный контроллер - по умолчанию включена csfr защита
     * @var Boolean
     */
    protected $secureController = true;
    protected $securityCode = null;
    
    protected $mapExtraPreImport = array();
    
    public function __construct(Prototyped $subject)
    {
        parent::__construct($subject);
        
        $this->
            getForm()->
                add(Primitive::string('securityCode'))->
                add(Primitive::string('return'))->
                add(Primitive::string('go'))->
                add(Primitive::boolean('cancel'));
        
        $this->
            map->
                addSource('return', RequestType::get())->
                addSource('go', RequestType::get());
    }

    /**
     * Проверит запрашиваемую команду на возможность вызова и передаст управление дальше, если это возможно. Иначе редирект на ошибку
     *
     * @param HttpRequest запрос
     * @return ModelAndView модель и представление
     */
    public function handleRequest(HttpRequest $request)
    {
        $mav = ModelAndView::create();
        $form = $this->getForm();
        
        $this->
            map->
                importOne('action', $request)->
                importOne('id', $request);
        
        $actionLabel = $form->{$this->getActionMethod()}('action');
        if (isset($this->mapExtraPreImport[$actionLabel]) && $this->mapExtraPreImport[$actionLabel]) {
            foreach($this->mapExtraPreImport[$actionLabel] as $primitiveName) {
                if ($this->getForm()->exists($primitiveName)) {
                    $this->map->importOne($primitiveName, $request);
                }
            }
        }
        $this->process = $request->hasServerVar('REQUEST_METHOD') && $request->getServerVar('REQUEST_METHOD') == 'POST';
        
        $this->initVars($request);

        $command = $form->{$this->getActionChoiseMethod()}('action');
        if ($this->hasObjectAndCommand()) {
            
            $this->prepareCommand($command, $request);
            $this->map->import($request);
            
            if ($form->getValue('id') instanceof Identifiable && !$this->process) {
                FormUtils::object2form($form->getValue('id'), $form, false);
                $form->markWrong('id');
            } elseif (!$form->getValue('id') instanceof Identifiable && !$this->process) {
                $protoList = $this->subject->proto()->getPropertyList();
                foreach($form->getPrimitiveNames() as $primitiveName) {
                    if (isset($protoList[$primitiveName]) && $protoList[$primitiveName]->getType() == 'boolean') {
                        $this->subject->proto()->importPrimitive($primitiveName, $form, $form->get($primitiveName), $this->subject);
                    }
                }
            }
            
            if ($this->checkPermissions($request)) {
                if ($form->getValue('cancel')) {
                    
                    $mav->setView(RedirectView::create($this->getCurrentUrl($request)));
                    
                } else {
                    $mav = $command->run($this->subject, $form, $request);

                    $mav = $this->postHandleRequest($mav, $request);
                }
            } else {
                $this->errorView(HttpStatus::CODE_403, $mav);
            }
        } else {
            $this->errorView(HttpStatus::CODE_404, $mav);
        }
        
        return $mav;
    }

    /**
     * @param ModelAndView модель и представление
     * @param HttpRequest запрос
     * @return ModelAndView
     */
    public function postHandleRequest(ModelAndView $mav, HttpRequest $request)
    {
        if ($this->isDisplayView($mav)) {
            $commandName = $this->getForm()->{$this->getActionMethod()}('action');
            
            if ($mav->getView() == self::COMMAND_SUCCEEDED) {
                
                $this->dropSecuritySessionVar($request);
                
                $redirect = $this->getCurrentUrl($request, true);
                if ($this->getForm()->getValue('go')) {
                    $go = base64_decode($this->getForm()->getValue('go'));
                    if ($go !== false) {
                        $redirect = $go;
                    }
                }
                
                $mav->
                    setView(RedirectView::create($redirect))->
                    getModel()->
                        drop('id');
            } else {                
                $mav->
                    setView($commandName ? get_class($this).ucfirst($commandName) : EmptyView::create())->
                    getModel()->
                        set('process', $this->process)->
                        set('curl', $this->getEncodedCurrentUrl($request))->
                        set('securityCode', $this->securityCode)->
                        set('form', $this->getForm())->
                        set('go', $this->getForm()->getValue('go'));
            }
        }

        return $mav;
    }
    
    protected function dropSecuritySessionVar(HttpRequest $request)
    {
        $commandName = $this->getForm()->{$this->getActionMethod()}('action');
                
        if ($this->secureController && !isset($this->mapUnsecureCommands[$commandName])) {
            $controller = get_class($this);
            if ( 
                $this->getForm()->getValue('securityCode') &&
                $request->hasSessionVar(self::SESSION_SECURITY_VAR) &&
                isset($request->getSessionVar(self::SESSION_SECURITY_VAR)[$controller]) &&
                isset($request->getSessionVar(self::SESSION_SECURITY_VAR)[$controller][$commandName]) &&
                isset($request->getSessionVar(self::SESSION_SECURITY_VAR)[$controller][$commandName][$this->getForm()->getValue('securityCode')])
            ) {
                $security = $request->getSessionVar(self::SESSION_SECURITY_VAR);
                unset($security[$controller][$commandName][$this->getForm()->getValue('securityCode')]);
                Session::assign(self::SESSION_SECURITY_VAR, $security);
            }
        }
        
        return $this;
    }

    /**
     * Если комманда с дополнительной проверкой прав - проверить их перед запуском, 
     * если нет - просто проверить что есть такая комманда.
     * 
     * @param HttpRequest $request
     * @return Boolean
     */
    protected function checkPermissions(HttpRequest $request)
    {
        $command = $this->getForm()->{$this->getActionChoiseMethod()}('action');
        $commandName = $this->getForm()->{$this->getActionMethod()}('action');
        
        if ($this->process && $this->secureController && !isset($this->mapUnsecureCommands[$commandName])) {
            $controller = get_class($this);
            $csfr = 
                $this->getForm()->getValue('securityCode') &&
                $request->hasSessionVar(self::SESSION_SECURITY_VAR) &&
                isset($request->getSessionVar(self::SESSION_SECURITY_VAR)[$controller]) &&
                isset($request->getSessionVar(self::SESSION_SECURITY_VAR)[$controller][$commandName]) &&
                isset($request->getSessionVar(self::SESSION_SECURITY_VAR)[$controller][$commandName][$this->getForm()->getValue('securityCode')]);
        } else {
            $csfr = true;
        }
        
        return $csfr && (!$command instanceof SecurityCommand || $command->checkPermissions($this->getForm()));
    }

    /**
     * Подготовит команду к работе
     *
     * @param EditorCommand команда
     * @param HttpRequest запрос
     * @return ModelAndView
     */
    public function prepareCommand(EditorCommand $command, HttpRequest $request)
    {
        $form = $this->getForm();
            
        if (method_exists($command, 'setForm')) {
            $command->setForm($form, $request);
        }
        
        return $this;
    }

    /**
     * Добавит команду в контейнер или заменит существующую при совпадении имен
     *
     * @param string имя команды в карте команд
     * @param EditorCommand объект команды
     * @return CommandContainer
     */
    protected function insertCommand($commandName, EditorCommand $command)
    {
        $this->commandMap[$commandName] = $command;

        return $this;
    }
    
    /**
     * Переопределяется в конкретном контроллере c проверкой вызова родителя.
     * Определяет если ли комманда и есть ли объект для выполнения в комманде
     * @return boolean
     */
    protected function hasObjectAndCommand()
    {
        return 
            $this->getForm()->{$this->getActionChoiseMethod()}('action') instanceof EditorCommand &&
            $this->checkDefaultActionAndObject();
    }
    
    /**
     * Проверка на наличие объекта по умолчанию, т. к. практически везде одна и та же логика,
     * или контроллер на добавление - или id Identifialable
     * @return boolean
     */
    protected function checkDefaultActionAndObject()
    {
        return
            $this->getForm()->{$this->getActionMethod()}('action') == self::ACTION_ADD || 
            $this->getForm()->getValue('id') instanceof Identifiable;
        
    }
    
    protected function addUnsecureCommandToMap($action)
    {
        $this->mapUnsecureCommands[$action] = $action;
        
        return $this;
    }
    
    /**
     * Добавить к предварительному импорту примитивы, в зависимости от выбранного действия
     * @param type $action - метка действия
     * @param type $primitive - дополнительный примитив для импорта
     * @return \CommandContainer
     */
    protected function addPreimportToMap($action, $primitive)
    {
        if (!isset($this->mapExtraPreImport[$action])) {
            $this->mapExtraPreImport[$action] = array();
        }
        
        $this->mapExtraPreImport[$action][] = $primitive;
        $this->mapExtraPreImport[$action] = array_unique($this->mapExtraPreImport[$action]);
        
        return $this;
    }
    
    /**
     * Подготовка окружения, при необходимости, перед вызовом комманды
     * @param HttpRequest $request
     * @return \CommandContainer
     */
    protected function initVars(HttpRequest $request)
    { 
        /**
         * Если данные не отправляются, а команда только подготавливается к работе - 
         * генерируем код для защиты от CSRF и кладем в сессию
         */
        if (!$this->process && $this->secureController) {
            $commandName = $this->getForm()->{$this->getActionMethod()}('action');
            if (!isset($this->mapUnsecureCommands[$commandName])) {
                $commandEditor = get_class($this);

                $codes = 
                    $request->hasSessionVar(self::SESSION_SECURITY_VAR) ? 
                    $request->getSessionVar(self::SESSION_SECURITY_VAR) : 
                    array($commandEditor => array($commandName => array()));

                if (!isset($codes[$commandEditor])) { $codes[$commandEditor] = array(); }
                if (!isset($codes[$commandEditor][$commandName])) { $codes[$commandEditor][$commandName] = array(); }

                $code = CommonUtils::genUuid();
                $this->securityCode = $codes[$commandEditor][$commandName][$code] = $code;

                Session::assign(self::SESSION_SECURITY_VAR, $codes);
            }
        }
        
        return $this;
        
    }
    
    /**
     * Получение метода класса Form для получения комманды в зависимости от настроек контроллера.
     * Если комманда не установлена - получать комманду по умолчанию или нет
     * @return String
     */
    final protected function getActionChoiseMethod()
    {
        return $this->isDefaultAction() ? "getActualChoiceValue" : "getChoiceValue";
    }
    
    /**
     * Получение метода класса Form для получения значия формы комманды (Label) в зависимости
     * от настроек контроллера. Если action не передан - получать ли значение по умолчанию или нет
     * @return String
     */
    final protected function getActionMethod()
    {
        return $this->isDefaultAction() ? "getValueOrDefault" : "getValue";
    }
    
    /**
     * Должен переопределяться в конроллере если необходимо использовать комманду по умолчанию.
     * Инзначально комманда должна обязательно передаваться, иначе будет ошибка.
     * @return boolean
     */
    protected function isDefaultAction() { return false; }
}