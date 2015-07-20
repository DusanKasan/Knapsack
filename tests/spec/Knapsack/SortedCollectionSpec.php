<?php

namespace spec\Knapsack;

use Knapsack\Callback\Argument;
use Knapsack\Collection;
use Knapsack\SortedCollection;
use PhpSpec\ObjectBehavior;

/**
 * @mixin SortedCollection
 */
class SortedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $sort = function ($a, $b) {
            return $a > $b;
        };
        $this->beConstructedWith([1, 3, 2], $sort);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SortedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_sorted()
    {
        $this->toArray()->shouldReturn([1, 2 => 2, 1 => 3]);
    }

    function it_can_be_sorted_using_keys()
    {
        $this->beConstructedWith(
            [3, 1, 2],
            function ($k1, $v1, $k2, $v2) {
                return $k1 < $k2;
            });

        $this->toArray()
            ->shouldReturn([2 => 2, 1 => 1, 0 => 3]);
    }

    function it_will_convert_arguments_to_collection()
    {
        $this->beConstructedWith(
            [[1, 2], [3, 4, 5], [6]],
            function (Collection $a, Collection $b) {
                return $a->size() > $b->size();
            }
        );

        $this
            ->resetKeys()
            ->toArray()
            ->shouldReturn([[6], [1, 2], [3, 4, 5]]);
    }

    function it_can_work_with_argument_template()
    {
        $function = function ($a, $b, $delta) {
            return $a + $delta < $b;
        };

        $this->beConstructedWith(
            [1, 2, 3, 4],
            $function,
            [Argument::item(), Argument::secondItem(), 2]
        );

        $this
            ->resetKeys()
            ->toArray()
            ->shouldReturn([4, 3, 2, 1]);
    }
}
