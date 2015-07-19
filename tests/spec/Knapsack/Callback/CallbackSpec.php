<?php

namespace spec\Knapsack\Callback;

use Knapsack\Callback\Callback;
use Knapsack\Collection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin Callback
 */
class CallbackSpec extends ObjectBehavior
{
    function let()
    {
        $f = function ($v) {
            return $v;
        };

        $this->beConstructedWith($f);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Callback::class);
    }

    function it_can_execute()
    {
        $value = 1;
        $key = 0;

        $this->executeWithKeyAndValue($key, $value)->shouldReturn($value);
    }

    function it_can_can_work_with_keys()
    {
        $f = function ($k, $v) {
            return $k + $v;
        };
        $this->beConstructedWith($f);

        $this->executeWithKeyAndValue(1, 2)->shouldReturn(3);
    }

    function it_can_convert_arguments_to_collection()
    {
        $f = function (Collection $v) {
            return $v->size();
        };
        $this->beConstructedWith($f);

        $this->executeWithKeyAndValue(1, [1, 2])->shouldReturn(2);
    }
}
