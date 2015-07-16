<?php

namespace spec\Knapsack\Exceptions;

use Knapsack\Exceptions\InvalidArgument;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RuntimeException;

class InvalidArgumentSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InvalidArgument::class);
        $this->shouldHaveType(RuntimeException::class);
    }
}
