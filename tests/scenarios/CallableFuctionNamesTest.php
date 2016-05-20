<?php

namespace DusanKasan\Knapsack\Tests\Scenarios;

use DusanKasan\Knapsack\Collection;
use PHPUnit_Framework_TestCase;

class CallableFunctionNamesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Example that it's possible to use callable function names as arguments.
     */
    public function testIt()
    {
        $result = Collection::from([2, 1])
            ->concat([3, 4])
            ->sort('\DusanKasan\Knapsack\compare')
            ->values()
            ->toArray();

        $expected = [1, 2, 3, 4];

        $this->assertEquals($expected, $result);
    }
}
