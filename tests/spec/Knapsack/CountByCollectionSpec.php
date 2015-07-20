<?php

namespace spec\Knapsack;

use Knapsack\Callback\Argument;
use Knapsack\Collection;
use Knapsack\CountByCollection;
use PhpSpec\ObjectBehavior;

/**
 * @mixin CountByCollection
 */
class CountByCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            [1, 2, 3, 4],
            function ($i) {
                return $i % 2 == 0 ? 'even' : 'odd';
            }
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountByCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_grouped()
    {
        $this
            ->toArray()
            ->shouldReturn(['odd' => 2, 'even' => 2]);
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
            ->shouldReturn(['even' => 1, 'odd' => 2]);
    }

    function it_can_work_with_argument_template()
    {
        $function = function ($item, $delta) {
            return $item + $delta > 3 == 0 ? 'higherThan3' : 'lowerThan3';
        };

        $this->beConstructedWith(
            [1, 2, 3, 4],
            $function,
            [Argument::item(), 1]
        );

        $this
            ->toArray()
            ->shouldReturn(['higherThan3' => 2, 'lowerThan3' => 2]);
    }
}
