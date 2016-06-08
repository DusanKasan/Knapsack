<?php

namespace DusanKasan\Knapsack\Tests\Helpers;

class Machine
{
    /**
     * @var string
     */
    private $name;

    /**
     * Machine constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}
