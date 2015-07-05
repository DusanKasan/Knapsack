<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\InterleavedCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin InterleavedCollection
 */
class InterleavedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([1, 2, 3, 4], ['a', 'b', 'c', 'd', 'e']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InterleavedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_only_have_distinct_values()
    {
        $this->resetKeys()->toArray()->shouldReturn([1, 'a', 2, 'b', 3, 'c', 4, 'd', 'e']);
    }
}
