<?php

namespace spec\Knapsack;

use Knapsack\Callback\Argument;
use Knapsack\Collection;
use Knapsack\PartitionedByCollection;
use PhpSpec\ObjectBehavior;

/**
 * @mixin PartitionedByCollection
 */
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

    function it_will_convert_arguments_to_collection()
    {
        $this->beConstructedWith(
            [[1, 2], [3, 4, 5], [6]],
            function (Collection $i) {
                return $i->size() < 2;
            }
        );

        $this
            ->toArray()
            ->shouldReturn([[[1, 2], [3, 4, 5]], [2 => [6]]]);
    }

    function it_can_work_with_argument_template()
    {
        $function = function ($item, $delta) {
            return $item + $delta > 3;
        };

        $this->beConstructedWith(
            [1, 2, 3, 4],
            $function,
            [Argument::item(), 1]
        );

        $this
            ->toArray()
            ->shouldReturn([[1, 2], [2 => 3, 3 => 4]]);
    }
}
