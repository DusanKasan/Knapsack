<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\ReversedCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin ReversedCollection
 */
class ReversedCollectionSpec extends ObjectBehavior
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
        $this->shouldHaveType(ReversedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_sorted()
    {
        $this->toArray()->shouldReturn([2 => 2, 1 => 3, 0 => 1]);
    }
}
