<?php

namespace spec\DusanKasan\Knapsack\Exceptions;

use DusanKasan\Knapsack\Exceptions\RuntimeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InvalidReturnValueSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RuntimeException::class);
    }
}
