<?php

namespace DusanKasan\Knapsack\Tests\Helpers;

class Invoker
{
    private $value = 1;

    public function multiply($param)
    {
        return $this->value * $param;
    }

    public function increment()
    {
        return $this->value + 1;
    }
}
