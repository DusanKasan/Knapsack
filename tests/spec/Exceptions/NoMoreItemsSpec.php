<?php

namespace spec\DusanKasan\Knapsack\Exceptions;

use DusanKasan\Knapsack\Exceptions\NoMoreItems;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RuntimeException;

/**
 * @mixin NoMoreItems
 */
class NoMoreItemsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NoMoreItems::class);
        $this->shouldHaveType(RuntimeException::class);
    }
}
