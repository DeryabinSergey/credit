<?php

final class SubjectType extends Enumeration
{
    const TYPE_FIZ          = 1;
    const TYPE_IP           = 2;
    const TYPE_IUR          = 3;
    const TYPE_YUR          = 3;

    protected $names = array(
        self::TYPE_FIZ  => 'физическое лицо',
        self::TYPE_IP   => 'индивидуальный предприниматель',
        self::TYPE_IUR  => 'юридическое лицо'
    );

    protected $shortNames = array(
        self::TYPE_FIZ  => 'ФЛ',
        self::TYPE_IP   => 'ИП',
        self::TYPE_IUR  => 'ЮЛ'
    );
    
    protected $shortName = null;

    public static function create($id)
    {
        return new self($id);
    }

    public static function getAnyId()
    {
        return self::TYPE_FIZ;
    }
		
    public function getShortNameList()
    {
        return $this->shortNames;
    }
    
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @return SubjectType
    **/
    public function setId($id)
    {
        parent::setId($id);
        
        $names = $this->getShortNameList();

        if (isset($names[$id])) {
            $this->shortName = $names[$id];
        } else
            throw new MissingElementException(get_class($this) . ' knows nothing about such id == '.$id);

        return $this;
    }
}