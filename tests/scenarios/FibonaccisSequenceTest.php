<?php

namespace scenarios\Knapsack;

use Knapsack\Collection;
use PHPUnit_Framework_TestCase;

class FibonaccisSequenceTest extends PHPUnit_Framework_TestCase
{
    public function testIt()
    {
        $collection = new Collection([[1, 1]]);
        $result = $collection
            ->iterate(function ($v) {
                return [$v[1], $v[0] + $v[1]];
            })
            ->map(function ($v) {
                return $v[0];
            })
            ->take(5)
            ->resetKeys()
            ->toArray();

        $this->assertEquals([1, 1, 2, 3, 5], $result);
    }
}
