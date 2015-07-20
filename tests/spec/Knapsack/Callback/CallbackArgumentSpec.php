<?php

namespace spec\Knapsack\Callback;

use Knapsack\Callback\CallbackArgument;
use Knapsack\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ReflectionClass;
use ReflectionParameter;

/**
 * @mixin CallbackArgument
 */
class CallbackArgumentSpec extends ObjectBehavior
{
    /**
     * @var ReflectionParameter
     */
    private $reflectionParameter;

    function let(
        ReflectionParameter $reflectionParameter,
        ReflectionClass $reflectionClass
    )
    {
        $reflectionParameter->getClass()->willReturn($reflectionClass);
        $reflectionClass->getName()->willReturn(null);

        $this->reflectionParameter = $reflectionParameter;
        $this->beConstructedWith($this->reflectionParameter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CallbackArgument::class);
    }

    function it_can_check_that_it_has_not_a_default_value()
    {
        $this->reflectionParameter->isDefaultValueAvailable()->willReturn(false);
        $this->hasValue()->shouldReturn(false);
    }

    function it_can_check_and_get_default_value_of_original_argument()
    {
        $this->reflectionParameter->isDefaultValueAvailable()->willReturn(true);
        $this->reflectionParameter->getDefaultValue()->willReturn(1);
        $this->hasValue()->shouldReturn(true);
        $this->getValue()->shouldReturn(1);
    }

    function it_can_set_default_value_if_original_argument_does_not_have_any()
    {
        $value = 2;
        $this->reflectionParameter->isDefaultValueAvailable()->willReturn(false);

        $this->setValue($value);

        $this->hasValue()->shouldReturn(true);
        $this->getValue()->shouldReturn($value);
    }

    function it_can_overwrite_original_default_value()
    {
        $value = 2;
        $this->reflectionParameter->isDefaultValueAvailable()->willReturn(true);
        $this->reflectionParameter->getDefaultValue()->willReturn(1);

        $this->setValue($value);

        $this->hasValue()->shouldReturn(true);
        $this->getValue()->shouldReturn($value);
    }

    function it_will_convert_the_default_value_to_collection_if_hinted_and_possible(
        ReflectionParameter $reflectionParameter,
        ReflectionClass $reflectionClass
    )
    {
        $this->reflectionParameter->isDefaultValueAvailable()->willReturn(false);
        $reflectionParameter->getClass()->willReturn($reflectionClass);
        $reflectionClass->getName()->willReturn(Collection::class);

        $this->beConstructedWith($reflectionParameter);
        $this->setValue([1, 2]);
        $this->getValue()->shouldBeLike(new Collection([1, 2]));
    }
}
