<?php

trait ViewInterface
{
    /**
     * Проверка на необходимость передачи данных в модель для отображения.
     * Если View пустое или редирект - не передавать ничего
     * @param ModelAndView $mav
     * @return boolean
     */
    protected function isDisplayView(ModelAndView $mav = null)
    {
        $view = $mav instanceof ModelAndView ? $mav->getView() : $this->view;
        
        return 
            !$view instanceof RedirectView &&
            !$view instanceof EmptyView &&
            !$view instanceof HttpErrorView;
    }
    
    protected function errorView($status, ModelAndView $mav = null)
    {
        $path = PATH_BASE . 'src/user/www/error/';
        $errorView = new HttpErrorView(new HttpStatus($status), $path, '.html');
        if ($mav instanceof ModelAndView) {
            $mav->setView($errorView);
        } else {
            $this->view = $errorView;
        }
        
        return $errorView;
    }
}