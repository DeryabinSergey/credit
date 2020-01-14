<?php

class ajaxCreditRequestOgrn extends baseAjax
{    
    public function  __construct() 
    {
        parent::__construct();
        
        $this->
            form->
                add(Primitive::enumeration('type')->of('SubjectType'))->
                add(Primitive::string('term'));
    }
    
    public function getModel(HttpRequest $request)
    {        
        $model = parent::getModel($request);
        $form = $this->form;
        $result = array();
        
        if ($form->getValue('term') && $form->getValue('type') instanceof SubjectType && $form->getValue('type')->getId() != SubjectType::TYPE_FIZ) {
            $request = json_encode(array('query' => $form->getValue('term'), 'type' => $form->getValue('type')->getId() == SubjectType::TYPE_IP ? 'INDIVIDUAL' : 'LEGAL'));
            $response = json_decode(file_get_contents("https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party", false, stream_context_create( array('http' => array('method' => 'POST', 'header' => "Content-Type: application/json\r\nAccept: application/json\r\nAuthorization: Token ".Constants::DADATA_TOKEN . PHP_EOL, 'content' => $request) ) )), "true");
            if (is_array($response) && isset($response['suggestions']) && $response['suggestions']) {
                foreach($response['suggestions'] as $item) {
                    $result[] = array('label' => $item['data']['ogrn']." ".$item['value'], 'value' => $item['data']['ogrn'], 'date' => $item['data']['ogrn_date'] ? Date::create($item['data']['ogrn_date'])->toFormatString('d.m.Y') : null, 'name' => $item['value']);
                }
            }
        }
            
        $model->set('list', $result);
        
        return $model;
    }
}