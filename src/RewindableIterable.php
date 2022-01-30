<?php

namespace DusanKasan\Knapsack;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @template TKey
 * @template TVal
 */
class RewindableIterable implements IteratorAggregate
{
    /**
     * @var callable(): Traversable<TKey, TVal>
     */
    private $factory;

    /**
     * @param callable(): iterable<TKey, TVal> $factory
     */
    public function __construct(callable $factory)
    {
        $this->factory = function() use ($factory): Traversable {
            $iterable = $factory();
            if (is_array($iterable)) {
                $iterable = new ArrayIterator($iterable);
            }
            return $iterable;
        };
    }

    /**
     * @return Traversable<TKey, TVal>
     */
    public function getIterator(): Traversable
    {
        return ($this->factory)();
    }
}