<?php

namespace spec\Knapsack;

use Knapsack\Collection;
use Knapsack\Exceptions\NoMoreItems;
use Knapsack\IteratingCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IteratingCollectionSpec extends ObjectBehavior
{

    function let()
    {
        $input = [[1, 1]];
        $filter = function ($v) {
            return [$v[1], $v[0] + $v[1]];
        };
        $this->beConstructedWith($input, $filter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IteratingCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_generate_items_following_method_and_passing_previous_item_as_argument()
    {
        $this->rewind();
        $this->valid()->shouldReturn(TRUE);
        $this->current()->shouldReturn([1, 1]);
        $this->next();
        $this->valid()->shouldReturn(TRUE);
        $this->current()->shouldReturn([1, 2]);
        $this->next();
        $this->valid()->shouldReturn(TRUE);
        $this->current()->shouldReturn([2, 3]);
    }

    function it_will_generate_items_and_keys_following_method_and_passing_previous_item_as_argument()
    {
        $input = [[1, 1]];
        $filter = function ($k, $v) {
            yield $k + $v[1];
            yield [$v[1], $v[0] + $v[1]];
        };
        $this->beConstructedWith($input, $filter);

        $this->rewind();
        $this->valid()->shouldReturn(TRUE);
        $this->key()->shouldReturn(0);
        $this->current()->shouldReturn([1, 1]);
        $this->next();
        $this->valid()->shouldReturn(TRUE);
        $this->key()->shouldReturn(1);
        $this->current()->shouldReturn([1, 2]);
        $this->next();
        $this->valid()->shouldReturn(TRUE);
        $this->key()->shouldReturn(3);
        $this->current()->shouldReturn([2, 3]);
    }

    function it_will_generate_new_items_but_not_keys_if_only_one_value_is_yielded()
    {
        $input = [[1, 1]];
        $filter = function ($v) {
            yield [$v[1], $v[0] + $v[1]];
        };
        $this->beConstructedWith($input, $filter);

        $this->rewind();
        $this->valid()->shouldReturn(TRUE);
        $this->current()->shouldReturn([1, 1]);
        $this->next();
        $this->valid()->shouldReturn(TRUE);
        $this->current()->shouldReturn([1, 2]);
        $this->next();
        $this->valid()->shouldReturn(TRUE);
        $this->current()->shouldReturn([2, 3]);
    }

    function it_will_stop_on_no_more_items_exception()
    {
        $input = [[1, 1]];
        $filter = function ($k, $v) {
            if ($k > 0) {
                throw new NoMoreItems;
            }

            yield $k + $v[1];
            yield [$v[1], $v[0] + $v[1]];
        };
        $this->beConstructedWith($input, $filter);

        $this->rewind();
        $this->valid()->shouldReturn(TRUE);
        $this->key()->shouldReturn(0);
        $this->current()->shouldReturn([1, 1]);
        $this->next();
        $this->valid()->shouldReturn(TRUE);
        $this->key()->shouldReturn(1);
        $this->current()->shouldReturn([1, 2]);
        $this->next();
        $this->valid()->shouldReturn(FALSE);
    }
}
