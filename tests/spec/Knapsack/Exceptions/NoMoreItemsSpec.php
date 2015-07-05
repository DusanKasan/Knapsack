<?php

namespace spec\Knapsack\Exceptions;

use Knapsack\Exceptions\NoMoreItems;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RuntimeException;

class NoMoreItemsSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NoMoreItems::class);
        $this->shouldHaveType(RuntimeException::class);
    }
}
