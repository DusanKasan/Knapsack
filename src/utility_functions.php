<?php

namespace DusanKasan\Knapsack;

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
 * Returns a sum of all arguments.
 *
 * @param int|float|double[] ...$values
 * @return number
 */
function sum(...$values)
{
    return array_sum($values);
}

/**
 * Returns the highest value from all arguments.
 *
 * @param int|float|double[] ...$values
 * @return mixed
 */
function max(...$values)
{
    return \max($values);
}

/**
 * Returns the lowest value from all arguments.
 *
 * @param int|float|double[] ...$values
 * @return mixed
 */
function min(...$values)
{
    return \min($values);
}
