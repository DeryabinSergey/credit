<?php

class HTMLMetaManager extends Singleton
{
    protected $javaScript = array();
    protected $nameSpace = array();
    protected $meta = array();
    
    protected $style = array();
    protected $link = array();
    
    protected $title = "";

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

    public function appenJavaScript($url)
    {
        return $this->appendJavaScript($url);
    }

    public function appendJavaScript($url)
    {
        $this->javaScript[] = $url;

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
}