<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\GroupedCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin GroupedCollection
 */
class GroupedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([1, 2, 3, 4],
            function ($i) {
                return $i % 2;
            });
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GroupedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_grouped()
    {
        $this->toArray()->shouldReturn([1 => [1, 3], 0 => [2, 4]]);
    }
}
