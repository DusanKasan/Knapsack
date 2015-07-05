<?php

namespace spec\Knapsack;

use ArrayIterator;
use Knapsack\Collection;
use Knapsack\DistinctCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin DistinctCollection
 */
class DistinctCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $iterator = new ArrayIterator([1, 5, 1, 3, 3, 4]);
        $this->beConstructedWith($iterator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DistinctCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_only_have_distinct_values()
    {
        $this->toArray()->shouldReturn([0 => 1, 1 => 5, 3 => 3, 5 => 4]);
    }
}
