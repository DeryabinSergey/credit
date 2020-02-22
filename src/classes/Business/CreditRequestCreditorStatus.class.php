<?php

final class CreditRequestCreditorStatus extends Enumeration
{
    const TYPE_INCOME                   = 1;
    const TYPE_CANCELED                 = 2;
    const TYPE_CONCIDERED               = 3;
    const TYPE_SUCCESS                  = 4;
    const TYPE_REJECT                   = 5;

    protected $names = array(
        self::TYPE_INCOME               => 'получена',
        self::TYPE_CANCELED             => 'отменена',
        self::TYPE_CONCIDERED           => 'рассматривается',
        self::TYPE_SUCCESS              => 'оформлен',
        self::TYPE_REJECT               => 'отказано'
    );

    public static function create($id)
    {
        return new self($id);
    }

    public static function getAnyId()
    {
        return self::TYPE_INCOME;
    }
}