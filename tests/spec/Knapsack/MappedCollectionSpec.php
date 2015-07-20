<?php

namespace spec\Knapsack;

use Knapsack\Callback\Argument;
use Knapsack\Collection;
use Knapsack\MappedCollection;
use PhpSpec\ObjectBehavior;
use tests\helpers\Knapsack\PlusOneAdder;

/**
 * @mixin MappedCollection
 */
class MappedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $iterator = [5, 1, 3, 4];
        $mapping = function ($item) {
            return $item + 1;
        };
        $this->beConstructedWith($iterator, $mapping);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MappedCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_mapped()
    {
        $this->toArray()->shouldReturn([6, 2, 4, 5]);
    }

    function it_can_map_with_keys()
    {
        $iterator = [5, 1, 3, 4];
        $mapping = function ($key, $item) {
            return $key;
        };
        $this->beConstructedWith($iterator, $mapping);
        $this->toArray()->shouldReturn([0, 1, 2, 3]);
    }

    function it_can_change_keys_during_mapping()
    {
        $iterator = [1, 2];
        $mapping = function ($key, $item) {
            yield $key + 1;
            yield $item;
        };
        $this->beConstructedWith($iterator, $mapping);
        $this->toArray()->shouldReturn([1 => 1, 2 => 2]);
    }

    function it_will_change_the_item_if_it_yields_one_value()
    {
        $iterator = [1, 2];
        $mapping = function ($key, $item) {
            yield $item + 1;
        };
        $this->beConstructedWith($iterator, $mapping);
        $this->toArray()->shouldReturn([2, 3]);
    }

    function it_can_use_instance_methods_as_filter()
    {
        $instance = new PlusOneAdder();
        $this->beConstructedWith([1, 2], [$instance, 'dynamicMethod']);
        $this->toArray()->shouldReturn([2, 3]);
    }

    function it_can_use_static_methods_as_filter()
    {
        $this->beConstructedWith([1, 2], [PlusOneAdder::class, 'staticMethod']);
        $this->toArray()->shouldReturn([2, 3]);
    }

    function it_will_convert_arguments_to_collection()
    {
        $this->beConstructedWith(
            [[1, 2], [3, 4, 5], [6]],
            function (Collection $i) {
                return $i->size();
            }
        );

        $this
            ->toArray()
            ->shouldReturn([2, 3, 1]);
    }

    function it_can_work_with_argument_template()
    {
        $function = function ($item, $delta) {
            return $item + $delta;
        };

        $this->beConstructedWith(
            [1, 2, 3, 4],
            $function,
            [Argument::item(), 1]
        );

        $this
            ->toArray()
            ->shouldReturn([2, 3, 4, 5]);
    }
}
