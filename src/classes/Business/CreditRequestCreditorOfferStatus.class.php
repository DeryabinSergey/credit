<?php

final class CreditRequestCreditorOfferStatus extends Enumeration
{
    /**
     * получено
     */
    const TYPE_INCOME                   = 1;
    /**
     * отклонено - предложение отклонил заемщик
     */
    const TYPE_CANCELED                 = 2;
    /**
     * отказано - предложение отклонил кредитор
     */
    const TYPE_REJECT                   = 3;
    const TYPE_ACCEPTED                 = 4;
    const TYPE_MEETING                  = 5;
    const TYPE_SUCCESS                  = 6;

    protected $names = array(
        self::TYPE_INCOME               => 'получено',
        self::TYPE_CANCELED             => 'отклонено',
        self::TYPE_REJECT               => 'отказано',
        self::TYPE_ACCEPTED             => 'принято',
        self::TYPE_MEETING              => 'назначена встреча',
        self::TYPE_SUCCESS              => 'оформлен'
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