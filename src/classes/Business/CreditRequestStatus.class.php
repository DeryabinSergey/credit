<?php

final class CreditRequestStatus extends Enumeration
{
    const TYPE_INCOME                   = 1;
    const TYPE_CONCIDERED               = 2;
    const TYPE_APPROVE                  = 3;
    const TYPE_CONFIRM                  = 4;
    const TYPE_SUCCESS                  = 5;
    const TYPE_REJECT                   = 6;

    protected $names = array(
        self::TYPE_INCOME               => 'получена',
        self::TYPE_CONCIDERED           => 'рассматривается',
        self::TYPE_APPROVE              => 'одобрено',
        self::TYPE_CONFIRM              => 'согласовано',
        self::TYPE_SUCCESS              => 'оформлен',
        self::TYPE_REJECT               => 'отклонено'
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