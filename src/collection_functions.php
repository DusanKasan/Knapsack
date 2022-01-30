<?php

namespace DusanKasan\Knapsack;

use DusanKasan\Knapsack\Exceptions\InvalidArgument;
use DusanKasan\Knapsack\Exceptions\ItemNotFound;
use DusanKasan\Knapsack\Exceptions\NoMoreItems;
use Generator;
use Iterator;
use ReflectionObject;
use Traversable;

/**
 * Converts $collection to array. If there are multiple items with the same key, only the last will be preserved.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @return array<TKey, TVal>
 */
function toArray(iterable $collection)
{
    return is_array($collection) ? $collection : iterator_to_array($collection);
}

/**
 * Returns a lazy collection of distinct items in $collection.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @return iterable<TKey, TVal>
 */
function distinct(iterable $collection): iterable
{
    $factory = function () use ($collection): Generator {
        $distinctValues = [];
        foreach ($collection as $key => $value) {
            if (!in_array($value, $distinctValues)) {
                $distinctValues[] = $value;
                yield $key => $value;
            }
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns number of items in $collection.
 *
 * @param iterable $collection
 * @return int
 */
function size($collection): int
{
    $result = 0;
    foreach ($collection as $_) {
        $result++;
    }

    return $result;
}

/**
 * Returns a non-lazy collection with items from $collection in reversed order.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @return iterable<TKey, TVal>
 */
function reverse(iterable $collection): iterable
{
    $factory = function () use ($collection): Generator {
        $array = [];
        foreach ($collection as $key => $value) {
            $array[] = [$key, $value];
        }

        foreach (array_reverse($array) as $item) {
            yield $item[0] => $item[1];
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of values from $collection (i.e. the keys are reset).
 *
 * @template TVal
 * @param iterable<mixed, TVal> $collection
 * @return iterable<int, TVal>
 */
function values(iterable $collection): iterable
{
    $factory = function () use ($collection): iterable {
        foreach ($collection as $value) {
            yield $value;
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of keys from $collection.
 *
 * @template TKey
 * @param iterable<TKey, mixed> $collection
 * @return iterable<int, TKey>
 */
function keys(iterable $collection): iterable
{
    $factory = function () use ($collection): Generator {
        foreach ($collection as $key => $value) {
            yield $key;
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of items from $collection repeated infinitely.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @return iterable<TKey, TVal>
 */
function cycle(iterable $collection): iterable
{
    $factory = function () use ($collection): Generator {
        while (true) {
            foreach ($collection as $key => $value) {
                yield $key => $value;
            }
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a non-lazy collection of shuffled items from $collection.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @return iterable<TKey, TVal>
 */
function shuffle(iterable $collection): iterable
{
    $factory = function () use ($collection): Traversable {
        $buffer = [];
        foreach ($collection as $key => $value) {
            $buffer[] = [$key, $value];
        }

        \shuffle($buffer);

        return dereferenceKeyValue($buffer);
    };

    return new RewindableIterable($factory);
}

/**
 * Returns true if $collection does not contain any items.
 *
 * @param iterable $collection
 * @return bool
 */
function isEmpty(iterable $collection): bool
{
    foreach ($collection as $_) {
        return false;
    }

    return true;
}

/**
 * Returns true if $collection does contain any items.
 *
 * @param iterable $collection
 * @return bool
 */
function isNotEmpty(iterable $collection)
{
    return !isEmpty($collection);
}

/**
 * Returns a collection where keys are distinct values from $collection and values are number of occurrences of each
 * value.
 *
 * @template TVal
 * @param iterable<mixed, TVal> $collection
 * @return iterable<TVal, int>
 */
function frequencies(iterable $collection): iterable
{
    return countBy(
        $collection,
        /**
         * @param mixed $item
         * @return mixed
         */
        function ($item) {
            return $item;
        }
    );
}

/**
 * Returns the first item of $collection or throws ItemNotFound if #collection is empty.
 *
 * @template TVal
 * @param iterable<mixed, TVal> $collection
 * @return TVal
 */
function first(iterable $collection)
{
    return get(values($collection), 0);
}

/**
 * Returns the last item of $collection or throws ItemNotFound if #collection is empty.
 *
 * @template TVal
 * @param iterable<mixed, TVal> $collection
 * @return TVal
 */
function last($collection)
{
    return first(reverse($collection));
}

/**
 * Returns a lazy collection of items of $collection where value of each item is set to the return value of calling
 * $function on its value and key.
 *
 * @template TKey
 * @template TVal
 * @template TRes
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey):TRes $function
 * @return iterable<TKey, TRes>
 */
function map(iterable $collection, callable $function): iterable
{
    $factory = function () use ($collection, $function): iterable {
        foreach ($collection as $key => $value) {
            yield $key => $function($value, $key);
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of items from $collection for which $function returns true.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): bool|null $function
 * @return iterable<TKey, TVal>
 */
function filter($collection, callable $function = null)
{
    if (null === $function) {
        $function =
            /**
             * @param mixed $value
             * @param mixed $_
             * @return bool
             */
            function ($value, $_): bool {
                return (bool)$value;
            };
    }

    $factory = function () use ($collection, $function): iterable {
        foreach ($collection as $key => $value) {
            if ($function($value, $key)) {
                yield $key => $value;
            }
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection with items from all $collections passed as argument appended together
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> ...$collections
 * @return iterable<TKey, TVal>
 */
function concat(iterable ...$collections): iterable
{
    $factory = function () use ($collections): iterable {
        foreach ($collections as $collection) {
            foreach ($collection as $key => $value) {
                yield $key => $value;
            }
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Reduces the collection to single value by iterating over the collection and calling $reduction while
 * passing $startValue and current key/item as parameters. The output of $function is used as $startValue in
 * next iteration. The output of $function on last element is the return value of this function.
 *
 * @template TKey
 * @template TVal
 * @template TRes
 * @param iterable<TKey, TVal> $collection
 * @param callable(TRes, TVal, TKey): TRes $function
 * @param TRes $startValue
 * @return TRes
 */
function reduce(iterable $collection, callable $function, $startValue)
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
 * @param iterable $collection
 * @param int $levelsToFlatten -1 to flatten everything
 * @return iterable
 */
function flatten(iterable $collection, int $levelsToFlatten = -1): iterable
{
    $factory = function () use ($collection, $levelsToFlatten): Generator {
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

    return new RewindableIterable($factory);
}

/**
 * Returns a non-lazy collection sorted using $function($item1, $item2, $key1, $key2). $function should
 * return true if first item is larger than the second and false otherwise.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TVal, TKey, TKey): bool|int $function
 * @return iterable<TKey, TVal>
 */
function sort(iterable $collection, callable $function): iterable
{
    $factory = function () use ($collection, $function): Traversable {
        $array = toArray(
            values(
                map(
                    $collection,
                    /**
                     * @param mixed $value
                     * @param mixed $key
                     * @return array
                     */
                    function ($value, $key): array {
                        return [$key, $value];
                    }
                )
            )
        );

        usort(
            $array,
            /**
             * @param mixed $a
             * @param mixed $b
             * @return int
             */
            function ($a, $b) use ($function): int {
                return (int)$function($a[1], $b[1], $a[0], $b[0]);
            }
        );

        return dereferenceKeyValue($array);
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection that is a part of $collection starting from $from position and ending in $to position.
 * If $to is not provided, the returned collection is contains all items from $from until end of $collection. All items
 * before $from are iterated over, but not included in result.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param int $from
 * @param int $to -1 to slice until end
 * @return iterable<TKey, TVal>
 */
function slice(iterable $collection, int $from, int $to = -1): iterable
{
    $factory = function () use ($collection, $from, $to): iterable {
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

    return new RewindableIterable($factory);
}

/**
 * Returns a non-lazy collection of items grouped by the result of $function.
 *
 * @template TKey
 * @template TVal
 * @template TRes
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): TRes $function
 * @return iterable<TRes, iterable<int, TVal>>
 */
function groupBy(iterable $collection, callable $function): iterable
{
    $factory = function () use ($collection, $function): array {
        $result = [];

        foreach ($collection as $key => $value) {
            $newKey = $function($value, $key);
            $result[$newKey][] = $value;
        }

        return $result;
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a non-lazy collection of items grouped by the value at given key. Ignores non-collection items and items
 * without the given keys
 *
 * @template TKey
 * @template TVal
 * @param iterable<iterable<TKey, TVal>> $collection
 * @param TKey $key
 * @return iterable<TVal, iterable<TKey, TVal>>
 */
function groupByKey(iterable $collection, $key): iterable
{
    $generatorFactory = function () use ($collection, $key): iterable {
        return groupBy(
            filter(
                $collection,
                /**
                 * @param mixed $item
                 * @return bool
                 */
                function ($item) use ($key): bool {
                    return is_iterable($item) && has($item, $key);
                }
            ),
            /**
             * @param mixed $value
             * @return mixed
             */
            function ($value) use ($key) {
                return get($value, $key);
            }
        );
    };

    return new RewindableIterable($generatorFactory);
}

/**
 * Executes $function for each item in $collection
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): void $function
 * @return iterable<TKey, TVal>
 */
function each(iterable $collection, callable $function): iterable
{
    $factory = function () use ($collection, $function): iterable {
        foreach ($collection as $key => $value) {
            $function($value, $key);

            yield $key => $value;
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns an item with $key key from $collection. If that key is not present, throws ItemNotFound.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param TKey $key
 * @return TVal
 */
function get(iterable $collection, $key)
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
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param TKey $key
 * @param TVal $default value returned if key is not found
 * @return TVal
 */
function getOrDefault(iterable $collection, $key, $default)
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
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): bool $function
 * @param TVal|null $default
 * @return TVal|null
 */
function find(iterable $collection, callable $function, $default = null)
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
 * @template TKey
 * @template TVal
 * @template TNewKey
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): TNewKey $function
 * @return iterable<TNewKey, TVal>
 */
function indexBy($collection, callable $function)
{
    $factory = function () use ($collection, $function): iterable {
        foreach ($collection as $key => $value) {
            yield $function($value, $key) => $value;
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a non-lazy collection of items whose keys are the return values of $function and values are the number of
 * items in this collection for which the $function returned this value.
 *
 * @template TKey
 * @template TVal
 * @template TRes
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): TRes $function
 * @return iterable<TRes, int>
 */
function countBy($collection, callable $function)
{
    return map(
        groupBy($collection, $function),
        /**
         * @param mixed $c
         * @return int
         */
        function ($c): int {
            return size($c);
        }
    );
}

/**
 * Returns true if $function returns true for every item in $collection
 *
 * @template TKey
 * @template TVal
 * @param iterable $collection
 * @param callable(TVal, TKey): bool $function
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
 * @template TKey
 * @template TVal
 * @param iterable $collection
 * @param callable(TVal, TKey): bool $function
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
 * @param iterable $collection
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
 * @template TKey
 * @template TVal
 * @template TRes
 * @param iterable<TKey, TVal> $collection
 * @param callable(TRes, TVal, TKey): TRes $function
 * @param TRes $startValue
 * @return TRes
 */
function reduceRight(iterable $collection, callable $function, $startValue)
{
    return reduce(reverse($collection), $function, $startValue);
}

/**
 * Returns a lazy collection of first $numberOfItems items of $collection.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param int $numberOfItems
 * @return iterable<TKey, TVal>
 */
function take(iterable $collection, int $numberOfItems): iterable
{
    return slice($collection, 0, $numberOfItems);
}

/**
 * Returns a lazy collection of all but first $numberOfItems items of $collection.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param int $numberOfItems
 * @return iterable<TKey, TVal>
 */
function drop(iterable $collection, int $numberOfItems): iterable
{
    return slice($collection, $numberOfItems);
}

/**
 * Returns a lazy collection of values, where first value is $value and all subsequent values are computed by applying
 * $function to the last value in the collection. By default this produces an infinite collection. However you can
 * end the collection by throwing a NoMoreItems exception.
 *
 * @template TVal
 * @param TVal $value
 * @param callable(TVal): TVal $function
 * @return iterable<int, TVal>
 */
function iterate($value, callable $function)
{
    $duplicated = duplicate($value);
    $factory = function () use ($duplicated, $function): iterable {
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

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of items from $collection for which $function returned true.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): bool $function
 * @return iterable<TKey, TVal>
 */
function reject(iterable $collection, callable $function): iterable
{
    return filter(
        $collection,
        /**
         * @param mixed $value
         * @param mixed $key
         * @return bool
         */
        function ($value, $key) use ($function): bool {
            return !$function($value, $key);
        }
    );
}

/**
 * Returns a lazy collection of items in $collection without the last $numberOfItems items.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param int $numberOfItems
 * @return iterable<TKey, TVal>
 */
function dropLast(iterable $collection, $numberOfItems = 1): iterable
{
    $factory = function () use ($collection, $numberOfItems): iterable {
        $buffer = [];

        foreach ($collection as $key => $value) {
            $buffer[] = [$key, $value];

            if (count($buffer) > $numberOfItems) {
                $val = array_shift($buffer);
                yield $val[0] => $val[1];
            }
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of items from $collection separated by $separator.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param TVal $separator
 * @return iterable<TKey|int, TVal>
 */
function interpose(iterable $collection, $separator): iterable
{
    $factory = function () use ($collection, $separator): iterable {
        foreach (take($collection, 1) as $key => $value) {
            yield $key => $value;
        }

        foreach (drop($collection, 1) as $key => $value) {
            yield $separator;
            yield $key => $value;
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of first item from first collection, first item from second, second from first and
 * so on. Accepts any number of collections.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> ...$collections
 * @return iterable<TKey, TVal>
 */
function interleave(iterable ...$collections): iterable
{
    $generatorFactory = function () use ($collections): iterable {
        /* @var Iterator[] $iterators */
        $iterators = array_map(
            function ($collection) {
                $it = iterableToIterator($collection);
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

    return new RewindableIterable($generatorFactory);
}

/**
 * Returns a lazy collection of items in $collection with $value added as first element. If $key is not provided
 * it will be next integer index.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param TVal $value
 * @param TKey|null $key
 * @return iterable<TKey, TVal>
 */
function prepend(iterable $collection, $value, $key = null): iterable
{
    $generatorFactory = function () use ($collection, $value, $key): iterable {
        if ($key === null) {
            yield $value;
        } else {
            yield $key => $value;
        }

        foreach ($collection as $key => $value) {
            yield $key => $value;
        }
    };

    return new RewindableIterable($generatorFactory);
}

/**
 * Returns a lazy collection of items in $collection with $value added as last element. If $key is not provided
 * it will be next integer index.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param TVal $value
 * @param TKey|null $key
 * @return iterable<TKey, TVal>
 */
function append(iterable $collection, $value, $key = null): iterable
{
    $generatorFactory = function () use ($collection, $value, $key): iterable {
        foreach ($collection as $k => $v) {
            yield $k => $v;
        }

        if ($key === null) {
            yield $value;
        } else {
            yield $key => $value;
        }
    };

    return new RewindableIterable($generatorFactory);
}

/**
 * Returns a lazy collection by removing items from $collection until first item for which $function returns false.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): bool $function
 * @return iterable<TKey, TVal>
 */
function dropWhile(iterable $collection, callable $function): iterable
{
    $factory = function () use ($collection, $function): iterable {
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

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of items from $collection until first item for which $function returns false.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): bool $function
 * @return iterable<TKey, TVal>
 */
function takeWhile($collection, callable $function)
{
    $factory = function () use ($collection, $function): iterable {
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

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection. A result of calling map and flatten(1)
 *
 * @template TKey
 * @template TVal
 * @template TRes
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): iterable<TRes> $function
 * @return iterable<int, TRes>
 */
function mapcat(iterable $collection, callable $function): iterable
{
    return flatten(map($collection, $function), 1);
}

/**
 * Returns a lazy collection [take($collection, $position), drop($collection, $position)]
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param int $position
 * @return iterable<int, iterable<TKey, TVal>>
 */
function splitAt(iterable $collection, int $position): iterable
{
    $factory = function () use ($collection, $position): Generator {
        yield take($collection, $position);
        yield drop($collection, $position);
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection [takeWhile($collection, $function), dropWhile($collection, $function)]
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey): bool $function
 * @return iterable<int, iterable<TKey, TVal>>
 */
function splitWith($collection, callable $function): iterable
{
    $factory = function () use ($collection, $function): iterable {
        yield takeWhile($collection, $function);
        yield dropWhile($collection, $function);
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection with items from $collection but values that are found in keys of $replacementMap
 * are replaced by their values.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param iterable<TVal, TVal> $replacementMap
 * @return iterable<TKey, TVal>
 */
function replace(iterable $collection, iterable $replacementMap): iterable
{
    $factory = function () use ($collection, $replacementMap): iterable {
        foreach ($collection as $key => $value) {
            yield $key => getOrDefault($replacementMap, $value, $value);
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of reduction steps.
 *
 * @template TKey
 * @template TVal
 * @template TRes
 * @param iterable<TKey, TVal> $collection
 * @param callable(TRes, TVal, TKey): TRes $function
 * @param TRes $startValue
 * @return iterable<int, TRes>
 */
function reductions(iterable $collection, callable $function, $startValue): iterable
{
    $factory = function () use ($collection, $function, $startValue): iterable {
        $tmp = duplicate($startValue);

        yield $tmp;
        foreach ($collection as $key => $value) {
            $tmp = $function($tmp, $value, $key);
            yield $tmp;
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of every nth ($step) item in  $collection.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param int $step
 * @return iterable<TKey, TVal>
 */
function takeNth(iterable $collection, int $step)
{
    $factory = function () use ($collection, $step): iterable {
        $index = 0;
        foreach ($collection as $key => $value) {
            if ($index % $step == 0) {
                yield $key => $value;
            }

            $index++;
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of collections of $numberOfItems items each, at $step step
 * apart. If $step is not supplied, defaults to $numberOfItems, i.e. the partitions
 * do not overlap. If a $padding collection is supplied, use its elements as
 * necessary to complete last partition up to $numberOfItems items. In case there are
 * not enough padding elements, return a partition with less than $numberOfItems items.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param int $numberOfItems
 * @param int $step
 * @param iterable $padding
 * @return iterable<int, iterable<TKey, TVal>>
 */
function partition(iterable $collection, int $numberOfItems, int $step = -1, iterable $padding = []): iterable
{
    $generator = function () use ($collection, $numberOfItems, $step, $padding): Generator {
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

        yield take(concat(dereferenceKeyValue($buffer), $padding), $numberOfItems);
    };

    return new RewindableIterable($generator);
}

/**
 * Returns a lazy collection created by partitioning $collection each time $function returned a different value.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param callable(TVal, TKey=): mixed $function
 * @return iterable<iterable<TKey, TVal>>
 */
function partitionBy(iterable $collection, callable $function): iterable
{
    $factory = function () use ($collection, $function): iterable {
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

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of $value repeated $times times. If $times is not provided the collection is infinite.
 *
 * @template TVal
 * @param TVal $value
 * @param int $times
 * @return iterable<int, TVal>
 */
function repeat($value, int $times = -1): iterable
{
    $factory = function () use ($value, $times): iterable {
        $tmpTimes = $times;

        while ($tmpTimes != 0) {
            yield $value;

            $tmpTimes = $tmpTimes < 0 ? -1 : $tmpTimes - 1;
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of numbers starting at $start, incremented by $step until $end is reached.
 *
 * @param int $start
 * @param int|null $end
 * @param int $step
 * @return iterable<int, int>
 */
function range(int $start = 0, int $end = null, int $step = 1): iterable
{
    $factory = function () use ($start, $end, $step): iterable {
        return iterate(
            $start,
            function (int $value) use ($step, $end): int {
                $result = $value + $step;

                if ($end !== null && $result > $end) {
                    throw new NoMoreItems;
                }

                return $result;
            }
        );
    };

    return new RewindableIterable($factory);
}

/**
 * Returns duplicated/cloned $input that has no relation to the original one. Used for making sure there are no side
 * effect in functions.
 *
 * @template TVal
 * @param TVal $input
 * @return TVal
 */
function duplicate($input)
{
    if (is_array($input)) {
        return toArray(
            map(
                $input,
                /**
                 * @param mixed $i
                 * @return mixed
                 */
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
 * @TODO: types
 * @param iterable $collection
 * @return Traversable
 */
function dereferenceKeyValue(iterable $collection): Traversable
{
    foreach ($collection as $value) {
        yield $value[0] => $value[1];
    }
}

/**
 * Realizes collection - turns lazy collection into non-lazy one by iterating over it and storing the key/values.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @return iterable<TKey, TVal>
 */
function realize(iterable $collection): iterable
{
    return dereferenceKeyValue(
        toArray(
            map(
                $collection,
                /**
                 * @param mixed $value
                 * @param mixed $key
                 * @return array
                 */
                function ($value, $key): array {
                    return [$key, $value];
                }
            )
        )
    );
}

/**
 * Returns the second item of $collection or throws ItemNotFound if $collection is empty or has 1 item.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @return TVal
 * @throws ItemNotFound
 */
function second($collection)
{
    return get(values($collection), 1);
}

/**
 * Combines $keys and $values into a lazy collection. The resulting collection has length equal to the size of smaller
 * argument.
 *
 * @template TKey
 * @template TVal
 * @param iterable<mixed, TKey> $keys
 * @param iterable<mixed, TVal> $values
 * @return iterable<TKey, TVal>
 */
function combine(iterable $keys, iterable $values): iterable
{
    $values = iterableToIterator($values);
    $factory = function () use ($keys, $values): iterable {
        foreach ($keys as $key) {
            if (!$values->valid()) {
                break;
            }

            yield $key => $values->current();
            $values->next();
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection without the items associated to any of the keys from $keys.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param iterable<TKey> $keys
 * @return iterable<TKey, TVal>
 */
function except(iterable $collection, iterable $keys): iterable
{
    $keys = toArray(values($keys));

    return reject(
        $collection,
        /**
         * @param mixed $value
         * @param mixed $key
         * @return bool
         */
        function ($value, $key) use ($keys): bool {
            return in_array($key, $keys);
        }
    );
}

/**
 * Returns a lazy collection of items associated to any of the keys from $keys.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param iterable<TKey> $keys
 * @return iterable<TKey, TVal>
 */
function only($collection, $keys)
{
    $keys = toArray(values($keys));

    return filter(
        $collection,
        /**
         * @param mixed $_
         * @param mixed $key
         * @return bool
         */
        function ($_, $key) use ($keys): bool {
            return in_array($key, $keys, true);
        }
    );
}

/**
 * Returns a lazy collection of items that are in $collection but are not in any of the other arguments, indexed by the
 * keys from the first collection. Note that the ...$collections are iterated non-lazily.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param iterable<TKey, TVal> ...$collections
 * @return iterable<TKey, TVal>
 */
function diff(iterable $collection, ...$collections)
{
    $valuesToCompare = toArray(values(concat(...$collections)));
    $factory = function () use ($collection, $valuesToCompare): iterable {
        foreach ($collection as $key => $value) {
            if (!in_array($value, $valuesToCompare)) {
                yield $key => $value;
            }
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection of items that are in $collection and all the other arguments, indexed by the keys from the
 * first collection. Note that the ...$collections are iterated non-lazily.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param iterable<TVal> ...$collections
 * @return iterable<TKey, TVal>
 */
function intersect(iterable $collection, iterable ...$collections): iterable
{
    $valuesToCompare = toArray(values(concat(...$collections)));
    $factory = function () use ($collection, $valuesToCompare): iterable {
        foreach ($collection as $key => $value) {
            if (in_array($value, $valuesToCompare)) {
                yield $key => $value;
            }
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Returns a lazy collection where keys and values are flipped.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @return iterable<TVal, TKey>
 */
function flip($collection)
{
    $factory = function () use ($collection): iterable {
        foreach ($collection as $key => $value) {
            yield $value => $key;
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Checks for the existence of an item with key $key in $collection.
 *
 * @template TKey
 * @param iterable<TKey, mixed> $collection
 * @param TKey $key
 * @return bool
 */
function has(iterable $collection, $key): bool
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
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> ...$collections
 * @return iterable<int, iterable<TKey, TVal>>
 */
function zip(iterable ...$collections): iterable
{
    /* @var Iterator[] $iterators */
    $iterators = array_map(
        function ($collection) {
            $it = iterableToIterator($collection);
            $it->rewind();
            return $it;
        },
        $collections
    );

    $factory = function () use ($iterators): iterable {
        while (true) {
            $isMissingItems = false;
            $zippedItem = [];

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

    return new RewindableIterable($factory);
}

/**
 * Transpose each item in a collection, interchanging the row and column indexes.
 * Can only transpose collections of collections. Otherwise an InvalidArgument is raised.
 *
 * @template TVal
 * @param iterable<iterable<TVal>> $collection
 * @return iterable<iterable<TVal>>
 */
function transpose(iterable $collection): iterable
{
    if (some(
        $collection,
        /**
         * @param mixed $value
         * @return bool
         */
        function ($value): bool {
            return !is_iterable($value);
        }
    )) {
        throw new InvalidArgument('Can only transpose collections of collections.');
    }

    return array_map(
        function (...$items) {
            return $items;
        },
        ...toArray(
            map(
                $collection,
                function (iterable $c): array {
                    return toArray($c);
                }
            )
        )
    );
}

/**
 * Returns a lazy collection of data extracted from $collection items by dot separated key path. Supports the *
 * wildcard. If a key contains \ or * it must be escaped using \ character.
 *
 * @param iterable<mixed, mixed> $collection
 * @param string $keyPath
 * @return iterable<mixed, mixed>
 */
function extract(iterable $collection, string $keyPath): iterable
{
    preg_match_all('/(.*[^\\\])(?:\.|$)/U', $keyPath, $matches);
    $pathParts = $matches[1];

    $extractor = function (iterable $coll) use ($pathParts): iterable {
        foreach ($pathParts as $pathPart) {
            $coll = flatten(
                filter(
                    $coll,
                    /**
                     * @param mixed $item
                     * @return bool
                     */
                    function ($item) {
                        return is_iterable($item);
                    }
                ),
                1
            );

            if ($pathPart != '*') {
                $pathPart = str_replace(['\.', '\*'], ['.', '*'], $pathPart);
                $coll = values(only($coll, [$pathPart]));
            }
        }

        return $coll;
    };

    $factory = function () use ($collection, $extractor): iterable {
        foreach ($collection as $item) {
            foreach ($extractor([$item]) as $extracted) {
                yield $extracted;
            }
        }
    };

    return new RewindableIterable($factory);
}

/**
 * Checks whether $collection has exactly $size items.
 *
 * @param iterable $collection
 * @param int $size
 * @return bool
 */
function sizeIs(iterable $collection, int $size): bool
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
 * @param iterable $collection
 * @param int $size
 * @return bool
 */
function sizeIsLessThan(iterable $collection, int $size): bool
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
 * @param iterable $collection
 * @param int $size
 * @return bool
 */
function sizeIsGreaterThan(iterable $collection, int $size): bool
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
 * @param iterable $collection
 * @param int $fromSize
 * @param int $toSize
 * @return bool
 */
function sizeIsBetween(iterable $collection, int $fromSize, int $toSize): bool
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
 * @template TVal of numeric
 * @param iterable<TVal> $collection
 * @return TVal
 */
function sum(iterable $collection)
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
 * @param iterable<numeric> $collection
 * @return float
 */
function average($collection)
{
    $sum = 0;
    $count = 0;

    foreach ($collection as $value) {
        $sum += $value;
        $count++;
    }

    return (float)($count ? $sum / $count : 0);
}

/**
 * Returns maximal value from $collection.
 *
 * @template TVal of numeric
 * @param iterable<TVal> $collection
 * @return TVal
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
 * @template TVal of numeric
 * @param iterable<TVal> $collection
 * @return TVal
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
 * @param iterable $collection
 * @return string
 */
function toString($collection)
{
    $result = '';

    foreach ($collection as $value) {
        $result .= (string)$value;
    }

    return $result;
}


/**
 * Returns a lazy collection with items from $collection, but items with keys that are found in keys of $replacementMap
 * are replaced by their values.
 *
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $collection
 * @param iterable<TKey, TVal> $replacementMap
 * @return iterable<TKey, TVal>
 */
function replaceByKeys($collection, $replacementMap)
{
    $factory = function () use ($collection, $replacementMap): iterable {
        foreach ($collection as $key => $value) {
            $newValue = getOrDefault($replacementMap, $key, $value);
            yield $key => $newValue;
        }
    };

    return new RewindableIterable($factory);
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
function dump($input, int $maxItemsPerCollection = null, int $maxDepth = null)
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
                        $maxDepth > 0 ? $maxDepth - 1 : null
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

        $reflection = new ReflectionObject($input);
        $normalizedProperties = [];
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $normalizedProperties[$property->getName()] = $property->getValue($input);
        }
        return [get_class($input) => dump($normalizedProperties, null, $maxDepth > 0 ? $maxDepth - 1 : null)];
    }

    return gettype($input);
}

/**
 * Calls dump on $input and then prints it using the var_export. Returns $input.
 *
 *
 * @param iterable $input
 * @param int|null $maxItemsPerCollection
 * @param int|null $maxDepth
 * @return mixed
 */
function printDump(iterable $input, int $maxItemsPerCollection = null, int $maxDepth = null)
{
    var_export(dump($input, $maxItemsPerCollection, $maxDepth));
    return $input;
}
