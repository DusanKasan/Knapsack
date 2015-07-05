<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\DropLastCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin DropLastCollection
 */
class DropLastCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([1, 'a' => 2, 3, 4, 5], 3);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DropLastCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_only_have_distinct_values()
    {
        $this->toArray()->shouldReturn([1, 'a' => 2]);
    }
}
