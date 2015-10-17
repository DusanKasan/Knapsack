<?php

namespace spec\Knapsack\Exceptions;

use Knapsack\Exceptions\ItemNotFound;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RuntimeException;

/**
 * @mixin ItemNotFoundss
 */
class ItemNotFoundSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ItemNotFound::class);
        $this->shouldHaveType(RuntimeException::class);
    }
}
