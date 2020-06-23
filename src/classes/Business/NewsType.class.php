<?php

final class NewsType extends Enumeration
{
    const TYPE_MAIN		    = 1;
    const TYPE_CREDIT		    = 2;
    const TYPE_INVEST		    = 3;

    protected $names = array(
        self::TYPE_MAIN	    => 'заемщики',
        self::TYPE_CREDIT   => 'кредиторы',
        self::TYPE_INVEST   => 'инвесторы'
    );

    public static function create($id)
    {
        return new self($id);
    }

    public static function getAnyId()
    {
        return self::TYPE_MAIN;
    }
}