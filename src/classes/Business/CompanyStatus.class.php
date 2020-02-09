<?php

final class CompanyStatus extends Enumeration
{
    const TYPE_ACTIVE           = 1;
    const TYPE_LIQUIDATING      = 2;
    const TYPE_LIQUIDATED       = 3;
    const TYPE_REORGANIZING     = 4;

    protected $names = array(
        self::TYPE_ACTIVE           => 'действует',
        self::TYPE_LIQUIDATING      => 'ликвидируется',
        self::TYPE_LIQUIDATED       => 'ликвидирована',
        self::TYPE_REORGANIZING     => 'в процессе присоединения к другому юрлицу, с последующей ликвидацией'
    );
    
    protected $sid = array(
        self::TYPE_ACTIVE           => 'ACTIVE',
        self::TYPE_LIQUIDATING      => 'LIQUIDATING',
        self::TYPE_LIQUIDATED       => 'LIQUIDATED',
        self::TYPE_REORGANIZING     => 'REORGANIZING'
    );

    /**
     * @param Integer $id
     * @return CompanyStatus
     */
    public static function create($id)
    {
        return new self($id);
    }
    
    public static function createBySid($sid)
    {
        $list = 
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