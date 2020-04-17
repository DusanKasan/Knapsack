<?php

namespace DusanKasan\Knapsack;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

/**
 * Returns its argument.
 *
 * @param mixed $value
 * @return mixed
 */
function identity($value)
{
    return $value;
}

/**
 * Comparator. Returns a negative number, zero, or a positive number when x is logically 'less than', 'equal to', or
 * 'greater than' y.
 *
 * @param mixed $a
 * @param mixed $b
 * @return int
 */
function compare($a, $b)
{
    if ($a == $b) {
        return 0;
    }

    return $a < $b ? -1 : 1;
}

/**
 * Increments $value by one.
 *
 * @param int $value
 * @return int
 */
function increment($value)
{
    return $value + 1;
}

/**
 * Decrements $value by one.
 *
 * @param int $value
 * @return int
 */
function decrement($value)
{
    return $value - 1;
}

/**
 * Converts any iterable to an Iterator.
 *
 * @param iterable $iterable
 * @return Iterator
 */
function iterableToIterator(iterable $iterable): Iterator {
    if ($iterable instanceof IteratorAggregate) {
        $iterable = $iterable->getIterator();
    }

    if ($iterable instanceof Iterator) {
        return $iterable;
    }

    /** @var array $iterable */
    return new ArrayIterator($iterable);
}