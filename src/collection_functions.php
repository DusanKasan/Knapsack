<?php

namespace DusanKasan\Knapsack;

use DusanKasan\Knapsack\Exceptions\InvalidArgument;
use DusanKasan\Knapsack\Exceptions\ItemNotFound;
use DusanKasan\Knapsack\Exceptions\NoMoreItems;
use Iterator;
use IteratorIterator;
use Traversable;

/**
 * Converts $collection to array. If there are multiple items with the same key, only the last will be preserved.
 *
 * @param array|Traversable $collection
 * @return array
 */
function toArray($collection)
{
    return is_array($collection) ? $collection : iterator_to_array($collection);
}

/**
 * Returns a lazy collection of distinct items in $collection.
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function distinct($collection)
{
    $generatorFactory = function () use ($collection) {
        $distinctValues = [];

        foreach ($collection as $key => $value) {
            if (!in_array($value, $distinctValues)) {
                $distinctValues[] = $value;
                yield $key => $value;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns number of items in $collection.
 *
 * @param array|Traversable $collection
 * @return int
 */
function size($collection)
{
    $result = 0;
    foreach ($collection as $value) {
        $result++;
    }

    return $result;
}

/**
 * Returns a non-lazy collection with items from $collection in reversed order.
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function reverse($collection)
{
    $generatorFactory = function () use ($collection) {
        $array = [];
        foreach ($collection as $key => $value) {
            $array[] = [$key, $value];
        }

        return map(
            indexBy(
                array_reverse($array),
                function ($item) {
                    return $item[0];
                }
            ),
            function ($item) {
                return $item[1];
            }
        );
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of values from $collection (i.e. the keys are reset).
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function values($collection)
{
    $generatorFactory = function () use ($collection) {
        foreach ($collection as $value) {
            yield $value;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of keys from $collection.
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function keys($collection)
{
    $generatorFactory = function () use ($collection) {
        foreach ($collection as $key => $value) {
            yield $key;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of items from $collection repeated infinitely.
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function cycle($collection)
{
    $generatorFactory = function () use ($collection) {
        while (true) {
            foreach ($collection as $key => $value) {
                yield $key => $value;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a non-lazy collection of shuffled items from $collection.
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function shuffle($collection)
{
    $buffer = [];
    foreach ($collection as $key => $value) {
        $buffer[] = [$key, $value];
    }

    \shuffle($buffer);

    return dereferenceKeyValue($buffer);
}

/**
 * Returns true if $collection does not contain any items.
 *
 * @param array|Traversable $collection
 * @return bool
 */
function isEmpty($collection)
{
    foreach ($collection as $value) {
        return false;
    }

    return true;
}

/**
 * Returns true if $collection does contain any items.
 *
 * @param array|Traversable $collection
 * @return bool
 */
function isNotEmpty($collection)
{
    return !isEmpty($collection);
}

/**
 * Returns a collection where keys are distinct values from $collection and values are number of occurrences of each
 * value.
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function frequencies($collection)
{
    return countBy($collection, '\DusanKasan\Knapsack\identity');
}

/**
 * Returns the first item of $collection or throws ItemNotFound if #collection is empty.
 *
 * @param array|Traversable $collection
 * @return mixed
 */
function first($collection)
{
    return get(values($collection), 0);
}

/**
 * Returns the last item of $collection or throws ItemNotFound if #collection is empty.
 *
 * @param array|Traversable $collection
 * @return mixed
 */
function last($collection)
{
    return first(reverse($collection));
}

/**
 * Returns a lazy collection of items of $collection where value of each item is set to the return value of calling
 * $function on its value and key.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function map($collection, callable $function)
{
    $generatorFactory = function () use ($collection, $function) {
        foreach ($collection as $key => $value) {
            yield $key => $function($value, $key);
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of items from $collection for which $function returns true.
 *
 * @param array|Traversable $collection
 * @param callable|null $function ($value, $key)
 * @return Collection
 */
function filter($collection, callable $function = null)
{
    if (null === $function) {
        $function = function ($value) {
            return (bool) $value;
        };
    }

    $generatorFactory = function () use ($collection, $function) {
        foreach ($collection as $key => $value) {
            if ($function($value, $key)) {
                yield $key => $value;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection with items from all $collections passed as argument appended together
 *
 * @param array|Traversable ...$collections
 * @return Collection
 */
function concat(...$collections)
{
    $generatorFactory = function () use ($collections) {
        foreach ($collections as $collection) {
            foreach ($collection as $key => $value) {
                yield $key => $value;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Reduces the collection to single value by iterating over the collection and calling $reduction while
 * passing $startValue and current key/item as parameters. The output of $function is used as $startValue in
 * next iteration. The output of $function on last element is the return value of this function.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @param mixed $startValue
 * @return mixed
 */
function reduce($collection, callable $function, $startValue)
{
    $tmp = duplicate($startValue);

    foreach ($collection as $key => $value) {
        $tmp = $function($tmp, $value, $key);
    }

    return $tmp;
}

/**
 * Flattens multiple levels of nesting in collection. If $levelsToFlatten is not specified, flattens all levels of
 * nesting.
 *
 * @param array|Traversable $collection
 * @param int $levelsToFlatten -1 to flatten everything
 * @return Collection
 */
function flatten($collection, $levelsToFlatten = -1)
{
    $generatorFactory = function () use ($collection, $levelsToFlatten) {
        $flattenNextLevel = $levelsToFlatten < 0 || $levelsToFlatten > 0;
        $childLevelsToFlatten = $levelsToFlatten > 0 ? $levelsToFlatten - 1 : $levelsToFlatten;

        foreach ($collection as $key => $value) {
            if ($flattenNextLevel && (is_array($value) || $value instanceof Traversable)) {
                foreach (flatten($value, $childLevelsToFlatten) as $childKey => $childValue) {
                    yield $childKey => $childValue;
                }
            } else {
                yield $key => $value;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a non-lazy collection sorted using $collection($item1, $item2, $key1, $key2 ). $collection should
 * return true if first item is larger than the second and false otherwise.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value1, $value2, $key1, $key2)
 * @return Collection
 */
function sort($collection, callable $function)
{
    $array = iterator_to_array(
        values(
            map(
                $collection,
                function ($value, $key) {
                    return [$key, $value];
                }
            )
        )
    );

    uasort(
        $array,
        function ($a, $b) use ($function) {
            return $function($a[1], $b[1], $a[0], $b[0]);
        }
    );

    return dereferenceKeyValue($array);
}

/**
 * Returns a lazy collection that is a part of $collection starting from $from position and ending in $to position.
 * If $to is not provided, the returned collection is contains all items from $from until end of $collection. All items
 * before $from are iterated over, but not included in result.
 *
 * @param array|Traversable $collection
 * @param int $from
 * @param int $to -1 to slice until end
 * @return Collection
 */
function slice($collection, $from, $to = -1)
{
    $generatorFactory = function () use ($collection, $from, $to) {
        $index = 0;
        foreach ($collection as $key => $value) {
            if ($index >= $from && ($index < $to || $to == -1)) {
                yield $key => $value;
            } elseif ($index >= $to && $to >= 0) {
                break;
            }

            $index++;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a non-lazy collection of items grouped by the result of $function.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function groupBy($collection, callable $function)
{
    $result = [];

    foreach ($collection as $key => $value) {
        $newKey = $function($value, $key);

        $result[$newKey][] = $value;
    }

    return Collection::from($result)
        ->map(function ($entry) {
            return new Collection($entry);
        });
}

/**
 * Returns a non-lazy collection of items grouped by the value at given key. Ignores non-collection items and items
 * without the given keys
 *
 * @param array|Traversable $collection
 * @param mixed $key
 * @return Collection
 */
function groupByKey($collection, $key)
{
    $generatorFactory = function () use ($collection, $key) {

        return groupBy(
            filter(
                $collection,
                function ($item) use ($key) {
                    return isCollection($item) && has($item, $key);
                }
            ),
            function ($value) use ($key) {
                return get($value, $key);
            }
        );
    };

    return new Collection($generatorFactory);
}
/**
 * Executes $function for each item in $collection
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function each($collection, callable $function)
{
    $generatorFactory = function () use ($collection, $function) {
        foreach ($collection as $key => $value) {
            $function($value, $key);

            yield $key => $value;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns an item with $key key from $collection. If that key is not present, throws ItemNotFound.
 *
 * @param array|Traversable $collection
 * @param mixed $key
 * @return mixed
 */
function get($collection, $key)
{
    foreach ($collection as $valueKey => $value) {
        if ($key === $valueKey) {
            return $value;
        }
    }

    throw new ItemNotFound;
}

/**
 * Returns an item with $key key from $collection. If that key is not present, returns $default.
 *
 * @param array|Traversable $collection
 * @param mixed $key
 * @param mixed $default value returned if key is not found
 * @return mixed
 */
function getOrDefault($collection, $key, $default)
{
    try {
        return get($collection, $key);
    } catch (ItemNotFound $e) {
        return $default;
    }
}

/**
 * Returns the first item from $collection for which $function returns true. If item like that is not present, returns
 * $default.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @param mixed $default
 * @return mixed
 */
function find($collection, callable $function, $default = null)
{
    foreach ($collection as $key => $value) {
        if ($function($value, $key)) {
            return $value;
        }
    }

    return $default;
}

/**
 * Returns a lazy collection by changing keys of $collection for each item to the result of $function for
 * that item.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function indexBy($collection, callable $function)
{
    $generatorFactory = function () use ($collection, $function) {
        foreach ($collection as $key => $value) {
            yield $function($value, $key) => $value;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a non-lazy collection of items whose keys are the return values of $function and values are the number of
 * items in this collection for which the $function returned this value.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function countBy($collection, callable $function)
{
    return map(
        groupBy($collection, $function),
        '\DusanKasan\Knapsack\size'
    );
}

/**
 * Returns true if $function returns true for every item in $collection
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return bool
 */
function every($collection, callable $function)
{
    foreach ($collection as $key => $value) {
        if (!$function($value, $key)) {
            return false;
        }
    }

    return true;
}

/**
 * Returns true if $function returns true for at least one item in $collection.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return bool
 */
function some($collection, callable $function)
{
    foreach ($collection as $key => $value) {
        if ($function($value, $key)) {
            return true;
        }
    }

    return false;
}

/**
 * Returns true if $needle is found in $collection values.
 *
 * @param array|Traversable $collection
 * @param mixed $needle
 * @return bool
 */
function contains($collection, $needle)
{
    foreach ($collection as $key => $value) {
        if ($value === $needle) {
            return true;
        }
    }

    return false;
}

/**
 * Reduce that walks from right to the left.
 *
 * @param array|Traversable $collection
 * @param callable $function
 * @param mixed $startValue
 * @return mixed
 */
function reduceRight($collection, callable $function, $startValue)
{
    return reduce(reverse($collection), $function, $startValue);
}

/**
 * Returns a lazy collection of first $numberOfItems items of $collection.
 *
 * @param array|Traversable $collection
 * @param int $numberOfItems
 * @return Collection
 */
function take($collection, $numberOfItems)
{
    return slice($collection, 0, $numberOfItems);
}

/**
 * Returns a lazy collection of all but first $numberOfItems items of $collection.
 *
 * @param array|Traversable $collection
 * @param int $numberOfItems
 * @return Collection
 */
function drop($collection, $numberOfItems)
{
    return slice($collection, $numberOfItems);
}

/**
 * Returns a lazy collection of values, where first value is $value and all subsequent values are computed by applying
 * $function to the last value in the collection. By default this produces an infinite collection. However you can
 * end the collection by throwing a NoMoreItems exception.
 *
 * @param mixed $value
 * @param callable $function ($value, $key)
 * @return Collection
 */
function iterate($value, callable $function)
{
    $duplicated = duplicate($value);
    $generatorFactory = function () use ($duplicated, $function) {
        $value = $duplicated;

        yield $value;

        while (true) {
            try {
                $value = $function($value);
                yield $value;
            } catch (NoMoreItems $e) {
                break;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of items from $collection for which $function returned true.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function reject($collection, callable $function)
{
    return filter(
        $collection,
        function ($value, $key) use ($function) {
            return !$function($value, $key);
        }
    );
}

/**
 * Returns a lazy collection of items in $collection without the last $numberOfItems items.
 *
 * @param array|Traversable $collection
 * @param int $numberOfItems
 * @return Collection
 */
function dropLast($collection, $numberOfItems = 1)
{
    $generatorFactory = function () use ($collection, $numberOfItems) {
        $buffer = [];

        foreach ($collection as $key => $value) {
            $buffer[] = [$key, $value];

            if (count($buffer) > $numberOfItems) {
                $val = array_shift($buffer);
                yield $val[0] => $val[1];
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of items from $collection separated by $separator.
 *
 * @param array|Traversable $collection
 * @param mixed $separator
 * @return Collection
 */
function interpose($collection, $separator)
{
    $generatorFactory = function () use ($collection, $separator) {
        foreach (take($collection, 1) as $key => $value) {
            yield $key => $value;
        }

        foreach (drop($collection, 1) as $key => $value) {
            yield $separator;
            yield $key => $value;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of first item from first collection, first item from second, second from first and
 * so on. Accepts any number of collections.
 *
 * @param array|Traversable ...$collections
 * @return Collection
 */
function interleave(...$collections)
{
    $generatorFactory = function () use ($collections) {
        /* @var Iterator[] $iterators */
        $iterators = array_map(
            function ($collection) {
                $it = new IteratorIterator(new Collection($collection));
                $it->rewind();
                return $it;
            },
            $collections
        );

        do {
            $valid = false;
            foreach ($iterators as $it) {
                if ($it->valid()) {
                    yield $it->key() => $it->current();
                    $it->next();
                    $valid = true;
                }
            }
        } while ($valid);
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of items in $collection with $value added as first element. If $key is not provided
 * it will be next integer index.
 *
 * @param array|Traversable $collection
 * @param mixed $value
 * @param mixed|null $key
 * @return Collection
 */
function prepend($collection, $value, $key = null)
{
    $generatorFactory = function () use ($collection, $value, $key) {
        if ($key === null) {
            yield $value;
        } else {
            yield $key => $value;
        }

        foreach ($collection as $key => $value) {
            yield $key => $value;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of items in $collection with $value added as last element. If $key is not provided
 * it will be next integer index.
 *
 * @param array|Traversable $collection
 * @param mixed $value
 * @param mixed|null $key
 * @return Collection
 */
function append($collection, $value, $key = null)
{
    $generatorFactory = function () use ($collection, $value, $key) {
        foreach ($collection as $k => $v) {
            yield $k => $v;
        }

        if ($key === null) {
            yield $value;
        } else {
            yield $key => $value;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection by removing items from $collection until first item for which $function returns false.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function dropWhile($collection, callable $function)
{
    $generatorFactory = function () use ($collection, $function) {
        $shouldDrop = true;
        foreach ($collection as $key => $value) {
            if ($shouldDrop) {
                $shouldDrop = $function($value, $key);
            }

            if (!$shouldDrop) {
                yield $key => $value;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of items from $collection until first item for which $function returns false.
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function takeWhile($collection, callable $function)
{
    $generatorFactory = function () use ($collection, $function) {
        $shouldTake = true;
        foreach ($collection as $key => $value) {
            if ($shouldTake) {
                $shouldTake = $function($value, $key);
            }

            if ($shouldTake) {
                yield $key => $value;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection. A result of calling map and flatten(1)
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function mapcat($collection, callable $function)
{
    return flatten(map($collection, $function), 1);
}

/**
 * Returns a lazy collection [take($collection, $position), drop($collection, $position)]
 *
 * @param array|Traversable $collection
 * @param int $position
 * @return Collection
 */
function splitAt($collection, $position)
{
    $generatorFactory = function () use ($collection, $position) {
        yield take($collection, $position);
        yield drop($collection, $position);
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection [takeWhile($collection, $function), dropWhile($collection, $function)]
 *
 * @param array|Traversable $collection
 * @param callable $function ($value, $key)
 * @return Collection
 */
function splitWith($collection, callable $function)
{
    $generatorFactory = function () use ($collection, $function) {
        yield takeWhile($collection, $function);
        yield dropWhile($collection, $function);
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection with items from $collection but values that are found in keys of $replacementMap
 * are replaced by their values.
 *
 * @param array|Traversable $collection
 * @param array|Traversable $replacementMap
 * @return Collection
 */
function replace($collection, $replacementMap)
{
    $generatorFactory = function () use ($collection, $replacementMap) {
        foreach ($collection as $key => $value) {
            $newValue = getOrDefault($replacementMap, $value, $value);
            yield $key => $newValue;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of reduction steps.
 *
 * @param array|Traversable $collection
 * @param callable $function
 * @param mixed $startValue
 * @return Collection
 */
function reductions($collection, callable $function, $startValue)
{
    $generatorFactory = function () use ($collection, $function, $startValue) {
        $tmp = duplicate($startValue);

        yield $tmp;
        foreach ($collection as $key => $value) {
            $tmp = $function($tmp, $value, $key);
            yield $tmp;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of every nth ($step) item in  $collection.
 *
 * @param array|Traversable $collection
 * @param int $step
 * @return Collection
 */
function takeNth($collection, $step)
{
    $generatorFactory = function () use ($collection, $step) {
        $index = 0;
        foreach ($collection as $key => $value) {
            if ($index % $step == 0) {
                yield $key => $value;
            }

            $index++;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of collections of $numberOfItems items each, at $step step
 * apart. If $step is not supplied, defaults to $numberOfItems, i.e. the partitions
 * do not overlap. If a $padding collection is supplied, use its elements as
 * necessary to complete last partition up to $numberOfItems items. In case there are
 * not enough padding elements, return a partition with less than $numberOfItems items.
 *
 * @param array|Traversable $collection
 * @param int $numberOfItems
 * @param int $step
 * @param array|Traversable $padding
 * @return Collection
 */
function partition($collection, $numberOfItems, $step = -1, $padding = [])
{
    $generatorFactory = function () use ($collection, $numberOfItems, $step, $padding) {
        $buffer = [];
        $itemsToSkip = 0;
        $tmpStep = $step ?: $numberOfItems;

        foreach ($collection as $key => $value) {
            if (count($buffer) == $numberOfItems) {
                yield dereferenceKeyValue($buffer);

                $buffer = array_slice($buffer, $tmpStep);
                $itemsToSkip = $tmpStep - $numberOfItems;
            }

            if ($itemsToSkip <= 0) {
                $buffer[] = [$key, $value];
            } else {
                $itemsToSkip--;
            }
        }

        yield take(
            concat(dereferenceKeyValue($buffer), $padding),
            $numberOfItems
        );
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection created by partitioning $collection each time $function returned a different value.
 *
 * @param array|Traversable $collection
 * @param callable $function
 * @return Collection
 */
function partitionBy($collection, callable $function)
{
    $generatorFactory = function () use ($collection, $function) {
        $result = null;
        $buffer = [];

        foreach ($collection as $key => $value) {
            $newResult = $function($value, $key);

            if (!empty($buffer) && $result != $newResult) {
                yield dereferenceKeyValue($buffer);
                $buffer = [];
            }

            $result = $newResult;
            $buffer[] = [$key, $value];
        }

        if (!empty($buffer)) {
            yield dereferenceKeyValue($buffer);
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of $value repeated $times times. If $times is not provided the collection is infinite.
 *
 * @param mixed $value
 * @param int $times
 * @return Collection
 */
function repeat($value, $times = -1)
{
    $generatorFactory = function () use ($value, $times) {
        $tmpTimes = $times;

        while ($tmpTimes != 0) {
            yield $value;

            $tmpTimes = $tmpTimes < 0 ? -1 : $tmpTimes - 1;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of numbers starting at $start, incremented by $step until $end is reached.
 *
 * @param int $start
 * @param int|null $end
 * @param int $step
 * @return Collection
 */
function range($start = 0, $end = null, $step = 1)
{
    $generatorFactory = function () use ($start, $end, $step) {
        return iterate(
            $start,
            function ($value) use ($step, $end) {
                $result = $value + $step;

                if ($end !== null && $result > $end) {
                    throw new NoMoreItems;
                }

                return $result;
            }
        );
    };

    return new Collection($generatorFactory);
}

/**
 * Returns true if $input is array or Traversable object.
 *
 * @param mixed $input
 * @return bool
 */
function isCollection($input)
{
    return is_array($input) || $input instanceof Traversable;
}

/**
 * Returns duplicated/cloned $input that has no relation to the original one. Used for making sure there are no side
 * effect in functions.
 *
 * @param mixed $input
 * @return mixed
 */
function duplicate($input)
{
    if (is_array($input)) {
        return toArray(
            map(
                $input,
                function ($i) {
                    return duplicate($i);
                }
            )
        );
    } elseif (is_object($input)) {
        return clone $input;
    } else {
        return $input;
    }
}

/**
 * Transforms [[$key, $value], [$key2, $value2]] into [$key => $value, $key2 => $value2]. Used as a helper
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function dereferenceKeyValue($collection)
{
    $generatorFactory = function () use ($collection) {
        foreach ($collection as $value) {
            yield $value[0] => $value[1];
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Realizes collection - turns lazy collection into non-lazy one by iterating over it and storing the key/values.
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function realize($collection)
{
    return dereferenceKeyValue(
        toArray(
            map(
                $collection,
                function ($value, $key) {
                    return [$key, $value];
                }
            )
        )
    );
}

/**
 * Returns the second item of $collection or throws ItemNotFound if $collection is empty or has 1 item.
 *
 * @param array|Traversable $collection
 * @return mixed
 */
function second($collection)
{
    return get(values($collection), 1);
}

/**
 * Combines $keys and $values into a lazy collection. The resulting collection has length equal to the size of smaller
 * argument.
 *
 * @param array|Traversable $keys
 * @param array|Traversable $values
 * @return Collection
 */
function combine($keys, $values)
{
    $generatorFactory = function () use ($keys, $values) {
        $keyCollection = new Collection($keys);
        $valueIt = new IteratorIterator(new Collection($values));
        $valueIt->rewind();

        foreach ($keyCollection as $key) {
            if (!$valueIt->valid()) {
                break;
            }

            yield $key => $valueIt->current();
            $valueIt->next();
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection without the items associated to any of the keys from $keys.
 *
 * @param array|Traversable $collection
 * @param array|Traversable $keys
 * @return Collection
 */
function except($collection, $keys)
{
    $keys = toArray(values($keys));

    return reject(
        $collection,
        function ($value, $key) use ($keys) {
            return in_array($key, $keys);
        }
    );
}

/**
 * Returns a lazy collection of items associated to any of the keys from $keys.
 *
 * @param array|Traversable $collection
 * @param array|Traversable $keys
 * @return Collection
 */
function only($collection, $keys)
{
    $keys = toArray(values($keys));

    return filter(
        $collection,
        function ($value, $key) use ($keys) {
            return in_array($key, $keys, true);
        }
    );
}

/**
 * Returns a lazy collection of items that are in $collection but are not in any of the other arguments, indexed by the
 * keys from the first collection. Note that the ...$collections are iterated non-lazily.
 *
 * @param array|Traversable $collection
 * @param array|Traversable ...$collections
 * @return Collection
 */
function diff($collection, ...$collections)
{
    $valuesToCompare = toArray(values(concat(...$collections)));
    $generatorFactory = function () use ($collection, $valuesToCompare) {
        foreach ($collection as $key => $value) {
            if (!in_array($value, $valuesToCompare)) {
                yield $key => $value;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection of items that are in $collection and all the other arguments, indexed by the keys from the
 * first collection. Note that the ...$collections are iterated non-lazily.
 *
 * @param array|Traversable $collection
 * @param array|Traversable ...$collections
 * @return Collection
 */
function intersect($collection, ...$collections)
{
    $valuesToCompare = toArray(values(concat(...$collections)));
    $generatorFactory = function () use ($collection, $valuesToCompare) {
        foreach ($collection as $key => $value) {
            if (in_array($value, $valuesToCompare)) {
                yield $key => $value;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Returns a lazy collection where keys and values are flipped.
 *
 * @param array|Traversable $collection
 * @return Collection
 */
function flip($collection)
{
    $generatorFactory = function () use ($collection) {
        foreach ($collection as $key => $value) {
            yield $value => $key;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Checks for the existence of an item with key $key in $collection.
 *
 * @param array|Traversable $collection
 * @param mixed $key
 * @return bool
 */
function has($collection, $key)
{
    try {
        get($collection, $key);
        return true;
    } catch (ItemNotFound $e) {
        return false;
    }
}

/**
 * Returns a lazy collection of non-lazy collections of items from nth position from each passed collection. Stops when
 * any of the collections don't have an item at the nth position.
 *
 * @param array|Traversable ...$collections
 * @return Collection
 */
function zip(...$collections)
{
    /* @var Iterator[] $iterators */
    $iterators = array_map(
        function ($collection) {
            $it = new IteratorIterator(new Collection($collection));
            $it->rewind();
            return $it;
        },
        $collections
    );

    $generatorFactory = function () use ($iterators) {
        while (true) {
            $isMissingItems = false;
            $zippedItem = new Collection([]);

            foreach ($iterators as $it) {
                if (!$it->valid()) {
                    $isMissingItems = true;
                    break;
                }

                $zippedItem = append($zippedItem, $it->current(), $it->key());
                $it->next();
            }

            if (!$isMissingItems) {
                yield $zippedItem;
            } else {
                break;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Transpose each item in a collection, interchanging the row and column indexes.
 * Can only transpose collections of collections. Otherwise an InvalidArgument is raised.
 *
 * @param Collection[] $collection
 * @return Collection
 */
function transpose($collection)
{
    if (some($collection, function ($value) {
        return !($value instanceof Collection);
    })) {
        throw new InvalidArgument('Can only transpose collections of collections.');
    }

    return Collection::from(
        array_map(
            function (...$items) {
                return new Collection($items);
            },
            ...toArray(
                map(
                    $collection,
                    'DusanKasan\Knapsack\toArray'
                )
            )
        )
    );
}

/**
 * Returns a lazy collection of data extracted from $collection items by dot separated key path. Supports the *
 * wildcard. If a key contains \ or * it must be escaped using \ character.
 *
 * @param array|Traversable $collection
 * @param mixed $keyPath
 * @return Collection
 */
function extract($collection, $keyPath)
{
    preg_match_all('/(.*[^\\\])(?:\.|$)/U', $keyPath, $matches);
    $pathParts = $matches[1];

    $extractor = function ($coll) use ($pathParts) {
        foreach ($pathParts as $pathPart) {
            $coll = flatten(filter($coll, '\DusanKasan\Knapsack\isCollection'), 1);

            if ($pathPart != '*') {
                $pathPart = str_replace(['\.', '\*'], ['.', '*'], $pathPart);
                $coll = values(only($coll, [$pathPart]));
            }
        }

        return $coll;
    };

    $generatorFactory = function () use ($collection, $extractor) {
        foreach ($collection as $item) {
            foreach ($extractor([$item]) as $extracted) {
                yield $extracted;
            }
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Checks whether $collection has exactly $size items.
 *
 * @param array|Traversable $collection
 * @param int $size
 * @return bool
 */
function sizeIs($collection, $size)
{
    $itemsTempCount = 0;

    foreach ($collection as $key => $value) {
        $itemsTempCount++;

        if ($itemsTempCount > $size) {
            return false;
        }
    }

    return $itemsTempCount == $size;
}

/**
 * Checks whether $collection has less than $size items.
 *
 * @param array|Traversable $collection
 * @param int $size
 * @return bool
 */
function sizeIsLessThan($collection, $size)
{
    $itemsTempCount = 0;

    foreach ($collection as $key => $value) {
        $itemsTempCount++;

        if ($itemsTempCount > $size) {
            return false;
        }
    }

    return $itemsTempCount < $size;
}

/**
 * Checks whether $collection has more than $size items.
 *
 * @param array|Traversable $collection
 * @param int $size
 * @return bool
 */
function sizeIsGreaterThan($collection, $size)
{
    $itemsTempCount = 0;

    foreach ($collection as $key => $value) {
        $itemsTempCount++;

        if ($itemsTempCount > $size) {
            return true;
        }
    }

    return $itemsTempCount > $size;
}

/**
 * Checks whether $collection has between $fromSize to $toSize items. $toSize can be
 * smaller than $fromSize.
 *
 * @param array|Traversable $collection
 * @param int $fromSize
 * @param int $toSize
 * @return bool
 */
function sizeIsBetween($collection, $fromSize, $toSize)
{
    if ($fromSize > $toSize) {
        $tmp = $toSize;
        $toSize = $fromSize;
        $fromSize = $tmp;
    }

    $itemsTempCount = 0;
    foreach ($collection as $key => $value) {
        $itemsTempCount++;

        if ($itemsTempCount > $toSize) {
            return false;
        }
    }

    return $fromSize < $itemsTempCount && $itemsTempCount < $toSize;
}

/**
 * Returns a sum of all values in the $collection.
 *
 * @param array|Traversable $collection
 * @return int|float
 */
function sum($collection)
{
    $result = 0;

    foreach ($collection as $value) {
        $result += $value;
    }

    return $result;
}

/**
 * Returns average of values from $collection.
 *
 * @param array|Traversable $collection
 * @return int|float
 */
function average($collection)
{
    $sum = 0;
    $count = 0;

    foreach ($collection as $value) {
        $sum += $value;
        $count++;
    }

    return $count ? $sum/$count : 0;
}

/**
 * Returns maximal value from $collection.
 *
 * @param array|Traversable $collection
 * @return mixed
 */
function max($collection)
{
    $result = null;

    foreach ($collection as $value) {
        $result = $value > $result ? $value : $result;
    }

    return $result;
}

/**
 * Returns minimal value from $collection.
 *
 * @param array|Traversable $collection
 * @return mixed
 */
function min($collection)
{
    $result = null;
    $hasItem = false;

    foreach ($collection as $value) {
        if (!$hasItem) {
            $hasItem = true;
            $result = $value;
        }

        $result = $value < $result ? $value : $result;
    }

    return $result;
}

/**
 * Returns a string by concatenating the $collection values into a string.
 *
 * @param array|Traversable $collection
 * @return string
 */
function toString($collection)
{
    $result = '';

    foreach ($collection as $value) {
        $result .= (string) $value;
    }

    return $result;
}


/**
 * Returns a lazy collection with items from $collection, but items with keys  that are found in keys of $replacementMap
 * are replaced by their values.
 *
 * @param array|Traversable $collection
 * @param array|Traversable $replacementMap
 * @return Collection
 */
function replaceByKeys($collection, $replacementMap)
{
    $generatorFactory = function () use ($collection, $replacementMap) {
        foreach ($collection as $key => $value) {
            $newValue = getOrDefault($replacementMap, $key, $value);
            yield $key => $newValue;
        }
    };

    return new Collection($generatorFactory);
}

/**
 * Dumps a variable into scalar or array (recursively).
 *
 * - scalars are returned as they are,
 * - array of class name => properties (name => value and only properties accessible for this class)
 *   is returned for objects,
 * - arrays or Traversables are returned as arrays,
 * - for anything else result of calling gettype($input) is returned
 *
 * If specified, $maxItemsPerCollection will only leave specified number of items in collection,
 * appending a new element at end '>>>' if original collection was longer.
 *
 * If specified, $maxDepth will only leave specified n levels of nesting, replacing elements
 * with '^^^' once the maximum nesting level was reached.
 *
 * If a collection with duplicate keys is encountered, the duplicate keys (except the first one)
 * will be change into a format originalKey//duplicateCounter where duplicateCounter starts from
 * 1 at the first duplicate. So [0 => 1, 0 => 2] will become [0 => 1, '0//1' => 2]
 *
 * @param mixed $input
 * @param int|null $maxItemsPerCollection
 * @param int|null $maxDepth
 * @return array|mixed
 */
function dump($input, $maxItemsPerCollection = null, $maxDepth = null)
{
    if (is_scalar($input)) {
        return $input;
    }

    if (is_array($input) || $input instanceof Traversable) {
        if ($maxDepth === 0) {
            return '^^^';
        }

        $normalizedProperties = [];
        foreach ($input as $key => $value) {
            if ($maxItemsPerCollection !== null && count($normalizedProperties) >= $maxItemsPerCollection) {
                $normalizedProperties[] = '>>>';
                break;
            }

            for ($affix = 0; true; $affix++) {
                $betterKey = $affix ? "$key//$affix" : $key;
                if (!array_key_exists($betterKey, $normalizedProperties)) {
                    $normalizedProperties[$betterKey] = dump(
                        $value,
                        $maxItemsPerCollection,
                        $maxDepth>0 ? $maxDepth-1 : null
                    );

                    break;
                }
            }
        }

        return $normalizedProperties;
    }

    if (is_object($input)) {
        if ($maxDepth === 0) {
            return '^^^';
        }

        $reflection = new \ReflectionObject($input);
        $normalizedProperties = [];
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $normalizedProperties[$property->getName()] = $property->getValue($input);
        }
        return [get_class($input) => dump($normalizedProperties, null, $maxDepth>0 ? $maxDepth-1 : null)];
    }

    return gettype($input);
}

/**
 * Calls dump on $input and then prints it using the var_export. Returns $input.
 *
 * @param mixed $input
 * @param int|null $maxItemsPerCollection
 * @param int|null $maxDepth
 * @return mixed
 */
function printDump($input, $maxItemsPerCollection = null, $maxDepth = null)
{
    var_export(dump($input, $maxItemsPerCollection, $maxDepth));
    return $input;
}
