<?php

namespace scenarios\Knapsack;

use Knapsack\Collection;
use PHPUnit_Framework_TestCase;

class AutomaticItemToCollectionConversionTest extends PHPUnit_Framework_TestCase
{
    public function testIt()
    {
        $collection = new Collection([[1, 2], [3, 4, 5]]);
        $result = $collection
            ->map(function (Collection $i) {
                return $i->size();
            })
            ->toArray();

        $expected = [2, 3];

        $this->assertEquals($expected, $result);
    }
}
