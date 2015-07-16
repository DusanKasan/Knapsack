<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\PartitionedByCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PartitionedByCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([1, 3, 3, 2],
            function ($v) {
                return $v % 3 == 0;
            });
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PartitionedByCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_partitioned()
    {
        $this->toArray()->shouldReturn([[1], [1 => 3, 2 => 3], [3 => 2]]);
    }

    function it_can_be_partitioned_using_keys()
    {
        $this->beConstructedWith([1, 3, 3, 2],
            function ($k, $v) {
                return $k - $v;
            });

        $this->toArray()->shouldReturn([[1], [1 => 3], [2 => 3], [3 => 2]]);
    }
}
