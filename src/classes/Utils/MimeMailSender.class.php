<?php

class MimeMailSender
{
    protected $htmlTemplate = null;
    protected $textTemplate = null;
    
    protected $fromName = DEFAULT_MAILER;
    protected $fromEmail = DEFAULT_EMAIL;
    
    protected $toName = null;
    protected $toEmail = null;
    
    protected $subject = null;
    
    /**
     * @var PhpViewResolver
     */
    private $resolver = null;
    /**
     * @var Model
     */
    private $model = null;
    
    private $images = array();
    
    private $headers = array();
    
    /**
     * @var MimeMail
     */
    private $mail = null;
    
    public static function create($subject, $htmlTemplate, $textTemplate = null)
    {
        return new self($subject, $htmlTemplate, $textTemplate);
    }

    public function __construct($subject, $htmlTemplate, $textTemplate = null)
    {
        $this->subject = $subject;
        
        $this->htmlTemplate = $htmlTemplate;
        $this->textTemplate = $textTemplate;
        
        $this->resolver = PhpViewResolver::create(PATH_MAIL_TEMPLATES, EXT_TPL);
        $this->model = Model::create();
    }
		
    /**
     * @return MimeMailSender
    **/
    public function set($name, $var)
    {
        Assert::isNull($this->mail, 'You can`t add Variable to builded mail');
        
        $this->model->set($name, $var);

        return $this;
    }
    
    /**
     * @param string $path путь к изображению
     * @param string $id идентификатор изображения для использования в шаблоне
     * @return MimeMailSender
     */
    public function setImage($path, $id = null)
    {
        Assert::isNull($this->mail, 'You can`t add Image to builded mail');
        
        if (is_null($id)) $id = md5($path);
        
        $this->images[$id] = $path;
        
        return $this;
    }
    
    public function setHeader($header)
    {
        Assert::isNull($this->mail, 'You can`t add Header to builded mail');
        
        $this->headers[] = $header;
        
        return $this;
    }
    
    /**
     * @param string $email email получателя
     * @param string $name имя получателя
     * @return MimeMailSender
     */
    public function setTo($email, $name = null)
    {
        $this->toEmail = $email;
        if ($name) {
            $this->toName = $name;
        }
        
        return $this;
    }
    
    /**
     * @param string $email email отправителя
     * @param string $name имя отправителя
     * @return MimeMailSender
     */
    public function setFrom($email, $name = null)
    {
        $this->fromEmail = $email;
        if ($name) {
            $this->fromName = $name;
        }
        
        return $this;
    }
    
    /**
     * @param string $name имя отправителя
     * @return MimeMailSender
     */
    public function setFromName($name)
    {
        $this->fromName = $name;
        
        return $this;
    }
    
    /**
     * @param string $email электронная почта отправителя
     * @return MimeMailSender
     */
    public function setFromEmail($email)
    {
        $this->fromEmail = $email;
        
        return $this;
    }
    
    protected function build()
    {
        $this->mail = MimeMail::create();
        $mailParts = MimeMail::create()->setContentType('multipart/alternative');
                
        if ($this->textTemplate) {
            $mailParts->
                addPart(MimePart::create()->setBody($this->resolver->resolveViewName($this->textTemplate)->toString($this->model))->setContentType('text/plain')->setCharset("UTF-8")->setEncoding(MailEncoding::base64()));
        }
        
        $htmlMail = 
            MimeMail::create()->
                setContentType('multipart/related')->
                addPart(MimePart::create()->setBody($this->resolver->resolveViewName($this->htmlTemplate)->toString($this->model))->setContentType('text/html')->setCharset("UTF-8")->setEncoding(MailEncoding::base64()));
        
        foreach($this->images as $hash => $img) {
            $htmlMail->addPart(MimePart::create()->loadBodyFromFile($img)->setContentType(getimagesize($img)['mime'])->setEncoding(MailEncoding::base64())->setContentId($hash)->setInline());
        }

        $mailParts->addPart($htmlMail->build());
        
        $this->mail->addPart($mailParts->build())->build();
        
        return $this;     
    }

    public function getBody()
    {
        if (is_null($this->mail)) $this->build();
        
        return $this->mail->getEncodedBody();
    }
    
    public function getHeaders()
    {
        if (is_null($this->mail)) $this->build();
        
        return $this->headers ? implode("\r\n", array_merge($this->headers, $this->mail->getHeaders())) : $this->mail->getHeaders();
    }
    
    public function getFrom()
    {
        return MailAddress::create()->setAddress($this->fromEmail)->setPerson($this->fromName)->toString();
    }
    
    public function getTo()
    {
        $address = MailAddress::create()->setAddress($this->toEmail);
        if ($this->toName) {
            $address->setPerson($this->toName);
        }
        
        return $address->toString();
    }
    
    /**
     * Отправка письма
     * @return MimeMailSender
     * @throws MailNotSentException
     */
    public function send()
    {
        Mail::create()->
            setTo($this->getTo())->
            setFrom($this->getFrom())->
            setSubject($this->subject)->
            setHeaders($this->getHeaders())->
            setText($this->getBody())->  
	    setSendmailAdditionalArgs('-f '.$this->fromEmail)->
            send();
        
        return $this;
    }
}