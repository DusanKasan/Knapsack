<?php

namespace DusanKasan\Knapsack\Tests\Scenarios;

use DusanKasan\Knapsack\Collection;
use PHPUnit_Framework_TestCase;

class FibonaccisSequenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * Example generating first 5 values in fibonacci's sequence.
     */
    public function testIt()
    {
        $result = Collection::iterate([1, 1], function ($v) {
            return [$v[1], $v[0] + $v[1]];
        })
            ->map('\DusanKasan\Knapsack\first')
            ->take(5)
            ->values()
            ->toArray();

        $this->assertEquals([1, 1, 2, 3, 5], $result);
    }
}
