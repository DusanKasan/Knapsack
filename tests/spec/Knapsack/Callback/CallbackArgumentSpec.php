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
    function let()
    {
        $function = function($a) {
            return $a;
        };

        $reflectionParameter = new ReflectionParameter($function, 0);
        $this->beConstructedWith($reflectionParameter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CallbackArgument::class);
    }

    function it_can_check_that_it_has_not_a_default_value()
    {
        $this->hasValue()->shouldReturn(false);
    }

    function it_can_check_and_get_default_value_of_original_argument()
    {
        $function = function($a = 1) {
            return $a;
        };

        $reflectionParameter = new ReflectionParameter($function, 0);
        $this->beConstructedWith($reflectionParameter);

        $this->hasValue()->shouldReturn(true);
        $this->getValue()->shouldReturn(1);
    }

    function it_can_set_default_value_if_original_argument_does_not_have_any()
    {
        $value = 2;
        $this->setValue($value);

        $this->hasValue()->shouldReturn(true);
        $this->getValue()->shouldReturn($value);
    }

    function it_can_overwrite_original_default_value()
    {
        $function = function($a = 1) {
            return $a;
        };

        $reflectionParameter = new ReflectionParameter($function, 0);
        $this->beConstructedWith($reflectionParameter);

        $value = 2;
        $this->setValue($value);

        $this->hasValue()->shouldReturn(true);
        $this->getValue()->shouldReturn($value);
    }

    function it_will_convert_the_default_value_to_collection_if_hinted_and_possible()
    {
        $function = function(Collection $a) {
            return $a;
        };

        $reflectionParameter = new ReflectionParameter($function, 0);

        $this->beConstructedWith($reflectionParameter);
        $this->setValue([1, 2]);
        $this->getValue()->shouldBeLike(new Collection([1, 2]));
    }
}
