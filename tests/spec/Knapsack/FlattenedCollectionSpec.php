<?php

namespace spec\Knapsack;

use ArrayIterator;
use Knapsack\Collection;
use Knapsack\FlattenedCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin FlattenedCollection
 */
class FlattenedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $iterator = new ArrayIterator([1, [2, [3]]]);
        $this->beConstructedWith($iterator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FlattenedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_flattened()
    {
        $this->resetKeys()->toArray()->shouldReturn([1, 2, 3]);
    }

    function it_can_flatten_x_levels()
    {
        $iterator = [1, [2, [3]]];
        $this->beConstructedWith($iterator, 1);
        $this->resetKeys()->toArray()->shouldReturn([1, 2, [3]]);
    }
}
