<?php

final class ConfirmType extends Enumeration
{
    const TYPE_REGISTRATION_PHONE       = 1;
    const TYPE_RECOVERY_EMAIL           = 2;
    const TYPE_RECOVERY_PHONE           = 3;
    const TYPE_CREDIT_REQUEST           = 4;

    protected $names = array(
        self::TYPE_REGISTRATION_PHONE       => 'регистрация, телефон',
        self::TYPE_RECOVERY_EMAIL           => 'восстановление, email',
        self::TYPE_RECOVERY_PHONE           => 'восстановление, телефон',
        self::TYPE_CREDIT_REQUEST           => 'заявка на кредит',
    );

    public static function create($id)
    {
        return new self($id);
    }

    public static function getAnyId()
    {
        return self::TYPE_REGISTRATION_PHONE;
    }
}