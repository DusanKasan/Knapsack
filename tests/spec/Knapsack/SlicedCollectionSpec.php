<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\SlicedCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin SlicedCollection
 */
class SlicedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([1, 2, 3, 4, 5], 2, 4);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SlicedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_sliced()
    {
        $this->toArray()->shouldReturn([1 => 2, 2 => 3, 3 => 4]);
    }

    function it_can_slice_from_starting_point_to_the_end()
    {
        $this->beConstructedWith([1, 2, 3, 4, 5], 4, 0);
        $this->toArray()->shouldReturn([3 => 4, 4 => 5]);
    }
}
