<?php

abstract class viewBaseHelper
{
    const MODEL_NAME = 'helper';

    protected static $helperPath = 'parts/'; // путь для шаблонов по умолчанию
    protected $tempHelperPath = null; // если надо подменить путь к шаблонам
    protected $partViewer = null;
    protected $template	= null;

    /**
     * @param string $helperPath
     */
    public static function setHelperPath($helperPath = null)
    {
            self::$helperPath = $helperPath;
    }

    public function setTempHelperPath($tempHelperPath)
    {
            $this->tempHelperPath = $tempHelperPath;
            return $this;
    }

    /**
     * @param PartViewer $partViewer
     * @return viewBaseHelper
     */
    public function setPartViewer(PartViewer $partViewer)
    {
            $this->partViewer = $partViewer;
            return $this;
    }

    public function getPartViewer()
    {
            return $this->partViewer;
    }

    /**
     * @param string $templateName
     * @return viewBaseHelper
     */
    public function setTemplate($templateName = null)
    {
            $this->template = $templateName;
            return $this;
    }

    /**
     * @return string
     */
    public function dump()
    {
            $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
            $this->partViewer->
                    view(
                            $this->getPartViewerPath(),
                            Model::create()->set(self::MODEL_NAME, $this)
                    );

            $this->dropTempHelperPath();
    }

    /**
     * @return string
     */
    protected function getPartViewerPath()
    {
            return ($this->tempHelperPath ? $this->tempHelperPath : self::$helperPath)
                    .(is_null($this->template) ? get_class($this) : $this->template);
    }

    protected function dropTempHelperPath()
    {
            if ($this->tempHelperPath)
                    $this->tempHelperPath = null;

            return $this;
    }
}
?>