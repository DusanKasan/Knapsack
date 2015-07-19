<?php

namespace Knapsack\Callback;

class Argument
{
    const KEY = 'key';
    const ITEM = 'value';

    private $type;

    private function __construct($type)
    {
        $this->type = $type;
    }

    public static function KEY()
    {
        return new self(self::KEY);
    }

    public static function ITEM()
    {
        return new self(self::ITEM);
    }

    public function type()
    {
        return $this->type;
    }
}
