<?php

final class SecurityType extends Enumeration
{
    const TYPE_BASIC    = 1;
    const TYPE_NORMAL   = 2;
    const TYPE_STRONG   = 3;

    protected $names = array(
        self::TYPE_BASIC    => 'базовый',
        self::TYPE_NORMAL   => 'усиленный',
        self::TYPE_STRONG   => 'повышенный'
    );

    /**
     * @param Integer $id
     * @return SecurityType
     */
    public static function create($id)
    {
        return new self($id);
    }

    /**
     * Признак базовой безопасности
     *
     * @return Boolean
     */
    public function isBasic()
    {
        return $this->getId() == self::TYPE_BASIC;
    }

    /**
     * Признак повышенной безопасности
     *
     * @return Boolean
     */
    public function isNormal()
    {
        return $this->getId() == self::TYPE_NORMAL;
    }

    /**
     * Признак усиленной безопасности
     *
     * @return Boolean
     */
    public function isStrong()
    {
        return $this->getId() == self::TYPE_STRONG;
    }
}