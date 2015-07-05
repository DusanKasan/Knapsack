<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\SortedCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin SortedCollection
 */
class SortedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $sort = function ($a, $b) {
            return $a > $b;
        };
        $this->beConstructedWith([1, 3, 2], $sort);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SortedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_sorted()
    {
        $this->toArray()->shouldReturn([1, 2 => 2, 1 => 3]);
    }

    function it_can_be_sorted_using_keys()
    {
        $this->beConstructedWith(
            [3, 1, 2],
            function ($k1, $v1, $k2, $v2) {
                return $k1 < $k2;
            });

        $this->toArray()
            ->shouldReturn([2 => 2, 1 => 1, 0 => 3]);
    }
}
