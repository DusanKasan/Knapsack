<?php

namespace spec\Knapsack;

use ArrayIterator;
use Knapsack\CompleteRecursiveIteratorIterator;
use Knapsack\UniversalRecursiveIterator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RecursiveIteratorIterator;

/**
 * @mixin CompleteRecursiveIteratorIterator
 */
class CompleteRecursiveIteratorIteratorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(new UniversalRecursiveIterator(new ArrayIterator([1, [[3]]])));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompleteRecursiveIteratorIterator::class);
        $this->shouldHaveType(RecursiveIteratorIterator::class);
    }

    function it_will_iterate_correctly()
    {
        $this->setMaxDepth(1);
        $this->rewind();
        $this->valid()->shouldReturn(TRUE);
        $this->current()->shouldReturn(1);
        $this->callHasChildren()->shouldReturn(FALSE);
        $this->next();
        $this->valid()->shouldReturn(TRUE);
        $this->callHasChildren()->shouldReturn(FALSE);
        $this->current()->shouldReturn([3]);
        $this->next();
        $this->valid()->shouldReturn(FALSE);
    }
}
