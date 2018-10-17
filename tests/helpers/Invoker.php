<?php
/**
 * Created by PhpStorm.
 * User: nie_u
 * Date: 17.10.2018
 * Time: 22:33
 */

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
