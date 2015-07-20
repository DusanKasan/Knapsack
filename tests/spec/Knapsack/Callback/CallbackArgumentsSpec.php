<?php

namespace spec\Knapsack\Callback;

use Knapsack\Callback\Argument;
use Knapsack\Callback\CallbackArguments;
use PhpSpec\ObjectBehavior;
use ReflectionClass;
use ReflectionParameter;

/**
 * @mixin CallbackArguments
 */
class CallbackArgumentsSpec extends ObjectBehavior
{
    function let()
    {
        $function = function($a = 1, $b = 2) {
            return $a + $b;
        };

        $reflectionParameter1 = new ReflectionParameter($function, 0);
        $reflectionParameter2 = new ReflectionParameter($function, 1);

        $this->beConstructedWith([$reflectionParameter1, $reflectionParameter2]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CallbackArguments::class);
    }

    function it_can_resolve_arguments_if_all_of_them_have_value()
    {
        $this->resolve()->shouldReturn([1, 2]);
    }

    function it_can_resolve_arguments_from_template_without_placeholders()
    {
        $this->applyTemplate([2, 3]);
        $this->resolve()->shouldReturn([2, 3]);
    }

    function it_can_resolve_arguments_to_template()
    {
        $this->applyTemplate([2, Argument::key()]);
        $this->resolve([Argument::KEY => 1])->shouldReturn([2, 1]);
    }
}
