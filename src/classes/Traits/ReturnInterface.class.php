<?php

trait ReturnInterface
{
    protected $currentUrl = null;
    
    /**
     * Инициализация и возвращение текущего расположения
     * @param $request
     */
    final protected function getCurrentUrl(HttpRequest $request = null, $success = false, $successVarName = "success")
    {
        if (is_null($this->currentUrl)) {
            
            $curl = null;
            
            // Сначала пытаемся получить адрес из формы
            if (
                method_exists($this, 'getForm') &&
                $this->getForm() instanceof Form &&
                $this->getForm()->primitiveExists('return') &&
                $this->getForm()->getValue('return') &&
                ($decoded = base64_decode($this->getForm()->getValue('return'))) !== false
            ) {
                $curl = $decoded;
            } elseif (
                $request instanceof HttpRequest &&
                $request->hasGetVar('return') && 
                ($decoded = base64_decode($request->getGetVar('return'))) !== false
            ) {
                $curl = $decoded;
            } elseif ($this->getDefaultReturnUrl()) {
                $curl = $this->getDefaultReturnUrl();
            } elseif (
                $request instanceof HttpRequest &&
                $request->hasServerVar('REQUEST_URI')
            ) {
                $curl = defined('PATH_WEB') ? PATH_WEB . substr($request->getServerVar('REQUEST_URI'), 1) : $request->getServerVar('REQUEST_URI');
            } else {
                $curl = defined('PATH_WEB') ? PATH_WEB : '/';
            }
            
            $curl = CommonUtils::postProcessCurrentUrl($curl);
            
            $this->currentUrl = $curl;
        }
        
        if ($success) {
            $pos = strrpos($this->currentUrl, "#");
            $url = $pos === false ? $this->currentUrl : substr($this->currentUrl, 0, $pos);
            $appendix = $pos === false ? "" : substr($this->currentUrl, $pos);
            
            return $url . (strpos($url, "?") === false ? "?" : "&") . "{$successVarName}=1" . $appendix;
        } else {
            return $this->currentUrl;
        }
    }
    
    protected function getDefaultReturnUrl()
    {
        return false;
    }
    
    /**
     * Возвращает base64 кодированную строку с текущим адресом
     * @param HttpRequest $request
     */
    final protected function getEncodedCurrentUrl(HttpRequest $request = null, $success = false)
    {
    	return base64_encode($this->getCurrentUrl($request, $success));
    }
}