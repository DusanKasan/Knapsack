<?php

namespace spec\Knapsack\Callback;

use Knapsack\Callback\Argument;
use PhpSpec\ObjectBehavior;

/**
 * @mixin Argument
 */
class ArgumentSpec extends ObjectBehavior
{
    function it_is_initializable_as_key()
    {
        $this->beConstructedThrough('KEY');
        $this->shouldHaveType(Argument::class);
        $this->type()->shouldReturn(Argument::KEY);
    }

    function it_is_initializable_as_value()
    {
        $this->beConstructedThrough('ITEM');
        $this->type()->shouldReturn(Argument::ITEM);
    }
}
