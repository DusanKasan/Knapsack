<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\PartitionedCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin PartitionedCollection
 */
class PartitionedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([1, 3, 3, 2], 3, 2, [0, 1, 2]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PartitionedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_partitioned_with_step_and_padding()
    {
        $this->toArray()->shouldReturn([[1, 3, 3], [2 => 3, 3 => 2, 0 => 0]]);
    }

    function it_will_be_partitioned_with_step_and_without_padding()
    {
        $this->beConstructedWith([1, 3, 3, 2], 3, 2);
        $this->toArray()->shouldReturn([[1, 3, 3], [2 => 3, 3 => 2]]);
    }

    function it_will_be_partitioned_without_step_and_without_padding()
    {
        $this->beConstructedWith([1, 3, 3, 2], 3);
        $this->toArray()->shouldReturn([[1, 3, 3], [3 => 2]]);
    }
}
