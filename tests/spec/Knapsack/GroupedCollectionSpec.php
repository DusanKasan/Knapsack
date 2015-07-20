<?php

namespace spec\Knapsack;

use Knapsack\Callback\Argument;
use Knapsack\Collection;
use Knapsack\GroupedCollection;
use PhpSpec\ObjectBehavior;

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

    function it_will_convert_arguments_to_collection()
    {
        $this->beConstructedWith(
            [[1, 2], [3, 4, 5], [6]],
            function (Collection $i) {
                return $i->size() % 2 == 0 ? 'even' : 'odd';
            }
        );

        $this
            ->toArray()
            ->shouldReturn(['even' => [[1, 2]], 'odd' => [[3, 4, 5], [6]]]);
    }

    function it_can_work_with_argument_template()
    {
        $function = function ($item, $delta) {
            return $item + $delta > 3 ? 'higherThan3' : 'lowerThan3';
        };

        $this->beConstructedWith(
            [1, 2, 3, 4],
            $function,
            [Argument::item(), 1]
        );

        $this
            ->toArray()
            ->shouldReturn(['lowerThan3' => [1, 2], 'higherThan3' => [3, 4]]);
    }
}
