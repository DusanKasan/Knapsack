<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\ResetKeysCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin ResetKeysCollection
 */
class ResetKeysCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['a' => 1, 2, 'b' => 3, 4]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ResetKeysCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_reset_keys()
    {
        $this->toArray()->shouldReturn([1, 2, 3, 4]);
    }
}
