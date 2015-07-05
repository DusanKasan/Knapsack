<?php

namespace spec\Knapsack;

use ArrayIterator;
use Knapsack\Collection;
use Knapsack\ConcatenatedCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin ConcatenatedCollection
 */
class ConcatenatedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $iterator = new ArrayIterator([1, 2]);
        $secondIterator = new ArrayIterator([3, 4]);
        $this->beConstructedWith($iterator, $secondIterator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConcatenatedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_concat()
    {
        $this->toArray()->shouldReturn([1, 2, 2 => 3, 3 => 4]);
    }
}
