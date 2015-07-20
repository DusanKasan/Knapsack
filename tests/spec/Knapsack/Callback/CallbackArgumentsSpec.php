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
    /**
     * @var ReflectionParameter
     */
    private $parameter1;

    /**
     * @var ReflectionParameter
     */
    private $parameter2;

    function let(
        ReflectionParameter $parameter1,
        ReflectionParameter $parameter2,
        ReflectionClass $reflectionClass1,
        ReflectionClass $reflectionClass2
    )
    {
        $this->parameter1 = $parameter1;
        $this->parameter2 = $parameter2;

        $this->parameter1->isDefaultValueAvailable()->willReturn(true);
        $this->parameter1->getDefaultValue()->willReturn(1);
        $this->parameter1->getClass()->willReturn($reflectionClass1);
        $reflectionClass1->getName()->willReturn(null);

        $this->parameter2->isDefaultValueAvailable()->willReturn(true);
        $this->parameter2->getDefaultValue()->willReturn(2);
        $this->parameter2->getClass()->willReturn($reflectionClass2);
        $reflectionClass2->getName()->willReturn(null);

        $this->beConstructedWith([$this->parameter1, $this->parameter2]);
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
