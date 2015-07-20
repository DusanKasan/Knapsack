<?php

namespace Knapsack\Callback;

class Argument
{
    const KEY = 'key';
    const ITEM = 'value';
    const SECOND_KEY = 'secondKey';
    const SECOND_ITEM = 'secondItem';
    const INTERMEDIATE_VALUE = 'intermediateValue';

    /**
     * @var mixed
     */
    private $type;

    /**
     * @param mixed $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return Argument
     */
    public static function key()
    {
        return new self(self::KEY);
    }

    /**
     * @return Argument
     */
    public static function item()
    {
        return new self(self::ITEM);
    }

    /**
     * @return Argument
     */
    public static function secondKey()
    {
        return new self(self::SECOND_KEY);
    }

    /**
     * @return Argument
     */
    public static function secondItem()
    {
        return new self(self::SECOND_ITEM);
    }

    /**
     * @return Argument
     */
    public static function intermediateValue()
    {
        return new self(self::INTERMEDIATE_VALUE);
    }

    /**
     * @return mixed
     */
    public function type()
    {
        return $this->type;
    }
}
