<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\CycledCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin CycledCollection
 */
class CycledCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([1, 2, 3]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CycledCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_cycle()
    {
        $this
            ->take(6)
            ->resetKeys()
            ->toArray()
            ->shouldReturn([1, 2, 3, 1, 2, 3]);
    }
}
