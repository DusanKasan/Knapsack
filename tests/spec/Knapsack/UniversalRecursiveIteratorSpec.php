<?php

namespace spec\Knapsack;

use ArrayIterator;
use ArrayObject;
use IteratorIterator;
use Knapsack\UniversalRecursiveIterator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RecursiveIterator;

/**
 * @mixin UniversalRecursiveIterator
 */
class UniversalRecursiveIteratorSpec extends ObjectBehavior
{
    function let()
    {
        $a = new ArrayObject([1, 2, 3]);
        $b = new ArrayIterator([4, 5, 6, new ArrayObject([7])]);
        $c = [$a, $b, 8];
        $d = new ArrayIterator($c);

        $this->beConstructedWith($d);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UniversalRecursiveIterator::class);
        $this->shouldHaveType(RecursiveIterator::class);
        $this->shouldHaveType(IteratorIterator::class);
    }

    function it_can_iterate_correctly()
    {
        $this->rewind();
        $this->hasChildren()->shouldReturn(TRUE);
        $this->getChildren()->shouldBeLike(new UniversalRecursiveIterator(new ArrayIterator([1, 2, 3])));
        $this->next();
        $this->hasChildren()->shouldReturn(TRUE);
        $this->getChildren()
            ->shouldBeLike(new UniversalRecursiveIterator(new ArrayIterator([4, 5, 6, new ArrayObject([7])])));
        $this->next();
        $this->hasChildren()->shouldReturn(FALSE);
    }
}
