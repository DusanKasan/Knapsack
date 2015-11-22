<?php

namespace scenarios\Knapsack;

use Knapsack\Collection;
use PHPUnit_Framework_TestCase;
use stdClass;

class CallableFunctionNamesTest extends PHPUnit_Framework_TestCase
{
    public function testIt()
    {
        $result = Collection::from([2, 1])
            ->concat([3, 4])
            ->sort('\Knapsack\compare')
            ->values()
            ->toArray();

        $expected = [1, 2, 3, 4];

        $this->assertEquals($expected, $result);
    }
}
