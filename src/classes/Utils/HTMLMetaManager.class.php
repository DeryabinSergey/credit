<?php

class HTMLMetaManager extends Singleton
{
    protected $javaScript = array();
    protected $nameSpace = array();
    protected $meta = array();
    
    protected $style = array();
    protected $link = array();
    
    protected $title = "";
    protected $description = "";
    
    protected $footerJavaScript = "";

    /**
     * Позволяет добавить теги вида 
     * 
     * 
     * @param type $name
     * @param type $content
     * @param type $var
     * @return $this
     */
    public function appendMeta($name, $content, $var = 'name')
    {
        $this->meta[] = array('name' => $name, 'content' => $content, 'var' => $var);
        return $this;
    }

    public function appendLink($rel, $href, $type = null, $title = null)
    {
        $this->link[] = array('rel' => $rel, 'href' => $href, 'type' => $type, 'title' => $title);

        return $this;
    }

    public function getMetaList()
    {
        return $this->meta;
    }

    public function getLinkList()
    {
        return $this->link;
    }

    public function appendJavaScript($url)
    {
        $this->javaScript[] = $url . (mb_stripos($url, "?") === false ? "?" : "&") . ASSETS_HASH;

        return $this;
    }

    public function appendStyle($url)
    {
        $this->style[] = $url;

        return $this;
    }

    public function appendNameSpace($namespace)
    {
        $this->nameSpace[] = $namespace;

        return $this;
    }

    public function getJavaScriptList()
    {
        return $this->javaScript;
    }

    public function getNameSpaceList()
    {
        return $this->nameSpace;
    }

    public function getStyleList()
    {
        return $this->style;
    }
    
    public function setTitle($title) 
    {
    	$this->title = $title;
    	
    	return $this;
    }
    
    public function getTitle()
    {
    	return $this->title;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    public function appendFooterJavaScript($code)
    {
        $this->footerJavaScript = ($this->footerJavaScript ? "\r\n\r\n" : "") . $code;
    }
    
    public function getFooterJavaScript()
    {
        return $this->footerJavaScript;
    }
}