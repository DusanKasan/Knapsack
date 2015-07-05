<?php

namespace spec\Knapsack;

use DOMXPath;
use Knapsack\Collection;
use Knapsack\ForEachCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin ForEachCollection
 */
class ForEachCollectionSpec extends ObjectBehavior
{
    private $xpath;

    function let(DOMXPath $xpath)
    {
        $this->xpath = $xpath;
        $iterator = [$this->xpath];
        $callback = function (DOMXPath $item) {
            $item->query('asd');
        };
        $this->beConstructedWith($iterator, $callback);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ForEachCollection::class);
        $this->shouldHaveType(Collection::class);
    }

    function it_will_execute_callback_for_each()
    {
        $this->xpath->query('asd')->shouldBeCalled();
        $this->toArray()->shouldReturn([$this->xpath]);
    }

    function it_can_work_with_keys()
    {
        $iterator = [$this->xpath];
        $callback = function ($key, DOMXPath $item) {
            $item->query($key . 'asd');
        };
        $this->beConstructedWith($iterator, $callback);

        $this->xpath->query('0asd')->shouldBeCalled();
        $this->toArray()->shouldReturn([$this->xpath]);
    }
}
