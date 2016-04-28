<?php

namespace spec\DusanKasan\Knapsack\Exceptions;

use DusanKasan\Knapsack\Exceptions\RuntimeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin NonEqualCollectionLengthSpec
 */
class NonEqualCollectionLengthSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RuntimeException::class);
    }
}
