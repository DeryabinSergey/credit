<?php

class ajaxCreditRequestName extends baseAjax
{
    public function  __construct() 
    {
        parent::__construct();
        
        $this->
            form->
                add(Primitive::string('term'));
    }
    
    public function getModel(HttpRequest $request)
    {        
        $model = parent::getModel($request);
        $form = $this->form;
        $result = array();
        
        if ($form->getValue('term')) {
            $request = json_encode(array('query' => $form->getValue('term')));
            $response = json_decode(file_get_contents("https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/fio", false, stream_context_create( array('http' => array('method' => 'POST', 'header' => "Content-Type: application/json\r\nAccept: application/json\r\nAuthorization: Token 8e8269bebbb4d54cb70277e9fd2ea7955bc765b8" . PHP_EOL, 'content' => $request) ) )), "true");
            if (is_array($response) && isset($response['suggestions']) && $response['suggestions']) {
                foreach($response['suggestions'] as $item) {
                    $result[] = array('label' => $item['value'], 'value' => $item['value']);
                }
            }
        }
            
        $model->set('list', $result);
        
        return $model;
    }
}