<?php

class TelegramSender
{
    protected $template     = null; 
    
    /**
     * @var PhpViewResolver
     */
    private $resolver       = null;
    /**
     * @var Model
     */
    private $model          = null;

    private $text           = null;
    
    /**
     * @return TelegramSender
     */
    public static function create($template)
    {
        return new self($template);
    }

    /**
     * @return TelegramSender
     */
    public function __construct($template)
    {
        $this->template = $template;
        
        $this->resolver = PhpViewResolver::create(PATH_TELEGRAM_TEMPLATES, EXT_MARKDOWN);
        $this->model = Model::create();
    }
		
    /**
     * @return TelegramSender
    **/
    public function set($name, $var)
    {
        Assert::isNull($this->text, 'You can`t add Variable to builded mail');
        
        $this->model->set($name, $var);

        return $this;
    }
    
    /**
     * @return TelegramSender
    **/
    protected function build()
    {
        $this->text = $this->resolver->resolveViewName($this->template)->toString($this->model);
        
        return $this;     
    }
    
    /**
     * Отправка письма
     * @return MimeMailSender
     * @throws MailNotSentException
     */
    public function send($to, $force = false)
    {
        if (is_null($this->text) || $force) {
            $this->build();
        }

        $this->sendMessage($to, $this->text);
        
        return $this;
    }

    protected function sendMessage($chatId, $text)
    {
        file_get_contents(
            "https://api.telegram.org/bot".TELEGRAM_API."/sendMessage", 
            false, 
            stream_context_create(
                array(
                    'http' => array(
                        'method' => 'POST', 
                        'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 
                        'content' => http_build_query(array('chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'Markdown'))
                    )
                )
            )
        );        
    }
}