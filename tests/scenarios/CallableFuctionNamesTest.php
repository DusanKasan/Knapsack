<?php

namespace scenarios\Knapsack;

use Knapsack\Collection;
use PHPUnit_Framework_TestCase;
use stdClass;

class CallableFunctionNamesTest extends PHPUnit_Framework_TestCase
{
    public function testIt()
    {
        $collection = new Collection([2, 1]);
        $result = $collection
            ->concat([3, 4])
            ->sort('\Knapsack\compare')
            ->values()
            ->toArray();

        $expected = [1, 2, 3, 4];

        $this->assertEquals($expected, $result);
    }
}
