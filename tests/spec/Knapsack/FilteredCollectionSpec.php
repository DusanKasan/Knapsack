<?php

namespace spec\Knapsack;

use Knapsack\Callback\Argument;
use Knapsack\Collection;
use Knapsack\FilteredCollection;
use PhpSpec\ObjectBehavior;

/**
 * @mixin FilteredCollection
 */
class FilteredCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $input = [5, 1, 3, 4];
        $filter = function ($item) {
            return $item > 3;
        };
        $this->beConstructedWith($input, $filter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FilteredCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_filters()
    {
        $this->toArray()->shouldReturn([0 => 5, 3 => 4]);
    }

    function it_can_filter_with_keys()
    {
        $input = [5, 1, 3, 4];
        $filter = function ($key, $item) {
            return $key > 2;
        };
        $this->beConstructedWith($input, $filter);

        $this->toArray()->shouldReturn([3 => 4]);
    }

    function it_can_filter_and_automatically_convert_to_collection_in_callback()
    {
        $input = [[1, 2], [2, 3, 4]];
        $filter = function (Collection $c) {
            return $c->size() == 2;
        };
        $this->beConstructedWith($input, $filter);

        $this->toArray()->shouldReturn([[1, 2]]);
    }

    function it_can_work_with_argument_template()
    {
        $function = function ($item, $delta) {
            return ($item + $delta) % 2 == 0;
        };

        $this->beConstructedWith(
            [1, 2, 3, 4],
            $function,
            [Argument::item(), 1]
        );

        $this
            ->toArray()
            ->shouldReturn([1, 2 => 3]);
    }
}
