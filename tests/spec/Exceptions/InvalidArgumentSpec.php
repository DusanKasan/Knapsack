<?php

namespace spec\DusanKasan\Knapsack\Exceptions;

use DusanKasan\Knapsack\Exceptions\InvalidArgument;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RuntimeException;

/**
 * @mixin InvalidArgument
 */
class InvalidArgumentSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InvalidArgument::class);
        $this->shouldHaveType(RuntimeException::class);
    }
}
