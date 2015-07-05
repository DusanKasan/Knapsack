<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\CountByCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin CountByCollection
 */
class CountByCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([1, 2, 3, 4],
            function ($i) {
                return $i % 2 == 0 ? 'even' : 'odd';
            });
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountByCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_be_grouped()
    {
        $this->toArray()->shouldReturn(['odd' => 2, 'even' => 2]);
    }
}
