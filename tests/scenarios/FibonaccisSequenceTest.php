<?php

namespace scenarios\Knapsack;

use Knapsack\Collection;
use PHPUnit_Framework_TestCase;

class FibonaccisSequenceTest extends PHPUnit_Framework_TestCase
{
    public function testIt()
    {
        $result = Collection::iterate([1, 1], function ($v) {
            return [$v[1], $v[0] + $v[1]];
        })
            ->map('\Knapsack\first')
            ->take(5)
            ->values()
            ->toArray();

        $this->assertEquals([1, 1, 2, 3, 5], $result);
    }
}
