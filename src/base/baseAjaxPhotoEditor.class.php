<?php

abstract class baseAjaxPhotoEditor extends baseAjax implements UserController
{        
    public function  __construct() 
    {
        $this->secureController = true;
        
        parent::__construct();

        $this->
            form->
                add(
                    Primitive::choice('object')->
                        required()->
                        setList(
                            array(
                                'CreditRequestImage'            => 'CreditRequestImage'
                            )
                        )
                )->
                addWrongLabel('object', 'значение типа фотографий передано неверно')->
                addMissingLabel('object', 'не передано значение типа фотографий');
    }
}