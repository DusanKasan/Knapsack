<?php

namespace scenarios\Knapsack;

use Knapsack\Collection;
use PHPUnit_Framework_TestCase;
use stdClass;

class CallableFunctionNamesTest extends PHPUnit_Framework_TestCase
{
    public function testIt()
    {
        $collection = new Collection([true, 1, new stdClass(), 2]);
        $result = $collection
            ->reject('is_bool')
            ->reject('is_object')
            ->concat([3, 4])
            ->resetKeys()
            ->splitAt(2)
            ->toArray();

        $expected = [
            [
                1,
                2,
            ],
            [
                2 => 3,
                3 => 4,
            ]
        ];

        $this->assertEquals($expected, $result);
    }
}
