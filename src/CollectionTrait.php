<?php

namespace DusanKasan\Knapsack;

use DusanKasan\Knapsack\Exceptions\InvalidReturnValue;
use DusanKasan\Knapsack\Exceptions\ItemNotFound;
use Generator;

/**
 * @template TKey
 * @template TVal
 */
trait CollectionTrait
{
    /**
     * Converts $collection to array. If there are multiple items with the same key, only the last will be preserved.
     *
     * @return array
     */
    public function toArray()
    {
        return toArray($this->getItems());
    }

    /**
     * Returns a lazy collection of items for which $function returned true.
     *
     * @param callable(TVal, TKey): bool|null $function
     * @return static<TKey, TVal>
     */
    public function filter(callable $function = null)
    {
        return static::from(filter($this->getItems(), $function));
    }

    /**
     * Returns a lazy collection of distinct items. The comparison is the same as in in_array.
     *
     * @return static<TKey, TVal>
     */
    public function distinct()
    {
        return static::from(distinct($this->getItems()));
    }

    /**
     * Returns a lazy collection with items from all $collections passed as argument appended together
     *
     * @param iterable<TKey, TVal> ...$collections
     * @return static<TKey, TVal>
     */
    public function concat(...$collections)
    {
        return static::from(concat($this, ...$collections));
    }

    /**
     * Returns collection where each item is changed to the output of executing $function on each key/item.
     *
     * @template TRes
     * @param callable(TVal, TKey):TRes $function
     * @return static<TKey, TRes>
     */
    public function map(callable $function)
    {
        return static::from(map($this->getItems(), $function));
    }

    /**
     * Reduces the collection to single value by iterating over the collection and calling $function while
     * passing $startValue and current key/item as parameters. The output of $function is used as $startValue in
     * next iteration. The output of $function on last element is the return value of this function. If
     * $convertToCollection is true and the return value is a collection (iterable) an instance of Collection
     * is returned.
     *
     * @template TRes
     * @param callable $function
     * @param TRes $startValue
     * @param bool $convertToCollection
     * @return TRes|CollectionInterface<mixed, mixed>
     */
    public function reduce(callable $function, $startValue, $convertToCollection = false)
    {
        $result = reduce($this->getItems(), $function, $startValue);

        return ($convertToCollection && is_iterable($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns a lazy collection with one or multiple levels of nesting flattened. Removes all nesting when no value
     * is passed.
     *
     * @param int $depth -1 to flatten everything
     * @return CollectionInterface<mixed, mixed>
     */
    public function flatten($depth = -1)
    {
        return new Collection(flatten($this->getItems(), $depth));
    }

    /**
     * Returns a non-lazy collection sorted using $function($item1, $item2, $key1, $key2). $function should
     * return true if first item is larger than the second and false otherwise. In PHP8 and forward, $function
     * must return int as described by https://www.php.net/manual/en/function.usort.php.
     *
     * @param callable(TVal, TVal, TKey, TKey): bool|int $function
     * @return static<TKey, TVal>
     */
    public function sort(callable $function)
    {
        return static::from(\DusanKasan\Knapsack\sort($this->getItems(), $function));
    }

    /**
     * Returns lazy collection items of which are part of the original collection from item number $from to item
     * number $to. The items before $from are also iterated over, just not returned.
     *
     * @param int $from
     * @param int $to -1 to slice until end
     * @return static<TKey, TVal>
     */
    public function slice(int $from, int $to = -1)
    {
        return static::from(slice($this->getItems(), $from, $to));
    }

    /**
     * Returns collection which items are separated into groups indexed by the return value of $function.
     *
     * @template TRes
     * @param callable(TVal, TKey): TRes $function
     * @return CollectionInterface<TRes, static<int, TVal>>
     */
    public function groupBy(callable $function): CollectionInterface
    {
        return new Collection(function () use ($function): iterable {
            foreach (groupBy($this->getItems(), $function) as $k => $v) {
                yield $k => static::from($v);
            }
        });
    }

    /**
     * Returns collection where items are separated into groups indexed by the value at given key.
     *
     * @param mixed $key
     * @return CollectionInterface<mixed, mixed>
     */
    public function groupByKey($key): CollectionInterface
    {
        return new Collection(function () use ($key): iterable {
            foreach (groupByKey($this->getItems(), $key) as $k => $v) {
                yield $k => static::from($v);
            }
        });
    }

    /**
     * Returns a lazy collection in which $function is executed for each item.
     *
     * @param callable(TVal, TKey): void $function
     * @return static<TKey, TVal>
     */
    public function each(callable $function)
    {
        return static::from(each($this->getItems(), $function));
    }

    /**
     * Returns the number of items in this collection.
     *
     * @return int
     */
    public function size(): int
    {
        return size($this->getItems());
    }

    /**
     * Returns value at the key $key. If multiple values have this key, return first. If no value has this key, throw
     * ItemNotFound. If $convertToCollection is true and the return value is a collection (iterable) an
     * instance of Collection will be returned.
     *
     * @param TKey $key
     * @param bool $convertToCollection
     * @return TVal|CollectionInterface
     * @throws ItemNotFound
     */
    public function get($key, $convertToCollection = false)
    {
        $result = get($this->getItems(), $key);

        return ($convertToCollection && is_iterable($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns item at the key $key. If multiple items have this key, return first. If no item has this key, return
     * $ifNotFound. If no value has this key, throw ItemNotFound. If $convertToCollection is true and the return value
     * is a collection (iterable) an instance of Collection will be returned.
     *
     * @param TKey $key
     * @param TVal $default
     * @param bool $convertToCollection
     * @return TVal|CollectionInterface
     */
    public function getOrDefault($key, $default = null, $convertToCollection = false)
    {
        $result = getOrDefault($this->getItems(), $key, $default);

        return ($convertToCollection && is_iterable($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns first value matched by $function. If no value matches, return $default. If $convertToCollection is true
     * and the return value is a collection (iterable) an instance of Collection will be returned.
     *
     * @param callable(TVal, TKey): bool $function
     * @param TVal|null $default
     * @param bool $convertToCollection
     * @return TVal|CollectionInterface
     */
    public function find(callable $function, $default = null, $convertToCollection = false)
    {
        $result = find($this->getItems(), $function, $default);

        return ($convertToCollection && is_iterable($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns a non-lazy collection of items whose keys are the return values of $function and values are the number of
     * items in this collection for which the $function returned this value.
     *
     * @template TRes
     * @param callable(TVal, TKey): TRes $function
     * @return CollectionInterface<TRes, int>
     */
    public function countBy(callable $function): CollectionInterface
    {
        return new Collection(countBy($this->getItems(), $function));
    }

    /**
     * Returns a lazy collection by changing keys of this collection for each item to the result of $function for
     * that item.
     *
     * @template TNewKey
     * @param callable(TVal, TKey): TNewKey $function
     * @return static<TNewKey, TVal>
     */
    public function indexBy(callable $function)
    {
        return static::from(indexBy($this->getItems(), $function));
    }

    /**
     * Returns true if $function returns true for every item in this collection, false otherwise.
     *
     * @param callable(TVal, TKey): bool $function
     * @return bool
     */
    public function every(callable $function): bool
    {
        return every($this->getItems(), $function);
    }

    /**
     * Returns true if $function returns true for at least one item in this collection, false otherwise.
     *
     * @param callable(TVal, TKey): bool $function
     * @return bool
     */
    public function some(callable $function): bool
    {
        return some($this->getItems(), $function);
    }

    /**
     * Returns true if $value is present in the collection.
     *
     * @param TVal $value
     * @return bool
     */
    public function contains($value)
    {
        return contains($this->getItems(), $value);
    }

    /**
     * Returns collection of items in this collection in reverse order.
     *
     * @return static<TKey, TVal>
     */
    public function reverse()
    {
        return static::from(reverse($this->getItems()));
    }

    /**
     * Reduce the collection to single value. Walks from right to left. If $convertToCollection is true and the return
     * value is a collection (iterable) an instance of Collection is returned.
     *
     * @template TRes
     * @param callable $function
     * @param TRes $startValue
     * @param bool $convertToCollection
     * @return TRes|CollectionInterface
     */
    public function reduceRight(callable $function, $startValue, $convertToCollection = false)
    {
        $result = reduceRight($this->getItems(), $function, $startValue);

        return ($convertToCollection && is_iterable($result)) ? new Collection($result) : $result;
    }

    /**
     * A form of slice that returns first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return static<TKey, TVal>
     */
    public function take(int $numberOfItems)
    {
        return static::from(take($this->getItems(), $numberOfItems));
    }

    /**
     * A form of slice that returns all but first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return static<TKey, TVal>
     */
    public function drop(int $numberOfItems)
    {
        return static::from(drop($this->getItems(), $numberOfItems));
    }

    /**
     * Returns collection of values from this collection but with keys being numerical from 0 upwards.
     *
     * @return static<int, TVal>
     */
    public function values()
    {
        return static::from(values($this->getItems()));
    }

    /**
     * Returns a lazy collection without elements matched by $function.
     *
     * @param callable(TVal, TKey): bool $function
     * @return static<TKey, TVal>
     */
    public function reject(callable $function)
    {
        return static::from(reject($this->getItems(), $function));
    }

    /**
     * Returns a lazy collection of the keys of this collection.
     *
     * @return CollectionInterface<int, TKey>
     */
    public function keys()
    {
        return new Collection(keys($this->getItems()));
    }

    /**
     * Returns a lazy collection of items of this collection separated by $separator
     *
     * @param TVal $separator
     * @return static<TKey|int, TVal>
     */
    public function interpose($separator)
    {
        return static::from(interpose($this->getItems(), $separator));
    }

    /**
     * Returns a lazy collection with last $numberOfItems items skipped. These are still iterated over, just skipped.
     *
     * @param int $numberOfItems
     * @return static<TKey, TVal>
     */
    public function dropLast(int $numberOfItems = 1)
    {
        return static::from(dropLast($this->getItems(), $numberOfItems));
    }

    /**
     * Returns a lazy collection of first item from first collection, first item from second, second from first and
     * so on. Accepts any number of collections.
     *
     * @param iterable<TKey, TVal> ...$collections
     * @return static<TKey, TVal>
     */
    public function interleave(...$collections)
    {
        return static::from(interleave($this->getItems(), ...$collections));
    }

    /**
     * Returns an infinite lazy collection of items in this collection repeated infinitely.
     *
     * @return static<TKey, TVal>
     */
    public function cycle()
    {
        return static::from(cycle($this->getItems()));
    }

    /**
     * Returns a lazy collection of items of this collection with $value added as first element. If $key is not provided
     * it will be next integer index.
     *
     * @param TVal $value
     * @param TKey|null $key
     * @return static<TKey|null, TVal>
     */
    public function prepend($value, $key = null)
    {
        return static::from(prepend($this->getItems(), $value, $key));
    }

    /**
     * Returns a lazy collection of items of this collection with $value added as last element. If $key is not provided
     * it will be next integer index.
     *
     * @param TVal $value
     * @param TKey|null $key
     * @return static<TKey|null, TVal>
     */
    public function append($value, $key = null)
    {
        return static::from(append($this->getItems(), $value, $key));
    }

    /**
     * Returns a lazy collection by removing items from this collection until first item for which $function returns
     * false.
     *
     * @param callable(TVal, TKey): bool $function
     * @return static<TKey, TVal>
     */
    public function dropWhile(callable $function)
    {
        return static::from(dropWhile($this->getItems(), $function));
    }

    /**
     * Returns a lazy collection which is a result of calling map($function) and then flatten(1)
     *
     * @param callable $function
     * @return static
     */
    public function mapcat(callable $function)
    {
        return static::from(mapcat($this->getItems(), $function));
    }

    /**
     * Returns a lazy collection of items from the start of the ollection until the first item for which $function
     * returns false.
     *
     * @param callable $function
     * @return static
     */
    public function takeWhile(callable $function)
    {
        return static::from(takeWhile($this->getItems(), $function));
    }

    /**
     * Returns a collection of [take($position), drop($position)]
     *
     * @param int $position
     * @return static
     */
    public function splitAt($position)
    {
        return static::from(function () use ($position): Generator {
            foreach (splitAt($this->getItems(), $position) as $k => $v) {
                yield $k => static::from($v);
            }
        });
    }

    /**
     * Returns a collection of [takeWhile($predicament), dropWhile($predicament]
     *
     * @param callable $function
     * @return static
     */
    public function splitWith(callable $function)
    {
        return static::from(function () use ($function): Generator {
            foreach (splitWith($this->getItems(), $function) as $k => $v) {
                yield $k => static::from($v);
            }
        });
    }

    /**
     * Returns a lazy collection with items from this collection but values that are found in keys of $replacementMap
     * are replaced by their values.
     *
     * @param iterable $replacementMap
     * @return static
     */
    public function replace($replacementMap)
    {
        return static::from(replace($this->getItems(), $replacementMap));
    }

    /**
     * Returns a lazy collection of reduction steps.
     *
     * @param callable $function
     * @param mixed $startValue
     * @return static
     */
    public function reductions(callable $function, $startValue)
    {
        return static::from(reductions($this->getItems(), $function, $startValue));
    }

    /**
     * Returns a lazy collection of every nth item in this collection
     *
     * @param int $step
     * @return static
     */
    public function takeNth($step)
    {
        return static::from(takeNth($this->getItems(), $step));
    }

    /**
     * Returns a non-collection of shuffled items from this collection
     *
     * @return CollectionInterface
     */
    public function shuffle()
    {
        return static::from(\DusanKasan\Knapsack\shuffle($this->getItems()));
    }

    /**
     * Returns a lazy collection of collections of $numberOfItems items each, at $step step
     * apart. If $step is not supplied, defaults to $numberOfItems, i.e. the partitions
     * do not overlap. If a $padding collection is supplied, use its elements as
     * necessary to complete last partition up to $numberOfItems items. In case there are
     * not enough padding elements, return a partition with less than $numberOfItems items.
     *
     * @param int $numberOfItems
     * @param int $step
     * @param iterable $padding
     * @return static
     */
    public function partition($numberOfItems, $step = 0, $padding = [])
    {
        return static::from(function() use ($numberOfItems, $step, $padding) {
            $c = partition($this->getItems(), $numberOfItems, $step, $padding);
            foreach($c as $k => $v) {
                yield $k => static::from($v);
            }
        });
    }

    /**
     * Creates a lazy collection of collections created by partitioning this collection every time $function will
     * return different result.
     *
     * @param callable $function
     * @return static
     */
    public function partitionBy(callable $function)
    {
        return static::from(function() use ($function) {
            $c = partitionBy($this->getItems(), $function);
            foreach($c as $k => $v) {
                yield $k => static::from($v);
            }
        });
    }

    /**
     * Returns true if this collection is empty. False otherwise.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return isEmpty($this->getItems());
    }

    /**
     * Opposite of isEmpty.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return isNotEmpty($this->getItems());
    }

    /**
     * Returns a collection where keys are distinct items from this collection and their values are number of
     * occurrences of each value.
     *
     * @return static<TVal, int>
     */
    public function frequencies()
    {
        return static::from(frequencies($this->getItems()));
    }

    /**
     * Returns first item of this collection. If the collection is empty, throws ItemNotFound. If $convertToCollection
     * is true and the return value is a collection (iterable) an instance of Collection is returned.
     *
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws ItemNotFound
     */
    public function first($convertToCollection = false)
    {
        $result = first($this->getItems());

        return ($convertToCollection && is_iterable($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns last item of this collection. If the collection is empty, throws ItemNotFound. If $convertToCollection
     * is true and the return value is a collection (iterable) it is converted to Collection.
     *
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws ItemNotFound
     */
    public function last($convertToCollection = false)
    {
        $result = last($this->getItems());

        return ($convertToCollection && is_iterable($result)) ? new Collection($result) : $result;
    }

    /**
     * Realizes collection - turns lazy collection into non-lazy one by iterating over it and storing the key/values.
     *
     * @return static
     */
    public function realize()
    {
        return static::from(realize($this->getItems()));
    }

    /**
     * Returns the second item in this collection or throws ItemNotFound if the collection is empty or has 1 item. If
     * $convertToCollection is true and the return value is a collection (iterable) it is converted to
     * Collection.
     *
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws ItemNotFound
     */
    public function second($convertToCollection = false)
    {
        $result = second($this->getItems());

        return ($convertToCollection && is_iterable($result)) ? new Collection($result) : $result;
    }

    /**
     * Combines the values of this collection as keys, with values of $collection as values.  The resulting collection
     * has length equal to the size of smaller collection.
     *
     * @template TNewVal
     * @param iterable<mixed, TNewVal> $collection
     * @return static<TVal, TNewVal>
     * @throws ItemNotFound
     */
    public function combine(iterable $collection)
    {
        return static::from(combine($this->getItems(), $collection));
    }

    /**
     * Returns a lazy collection without the items associated to any of the keys from $keys.
     *
     * @param iterable<TKey> $keys
     * @return static<TKey, TVal>
     */
    public function except(iterable $keys)
    {
        return static::from(except($this->getItems(), $keys));
    }

    /**
     * Returns a lazy collection of items associated to any of the keys from $keys.
     *
     * @param iterable<TKey> $keys
     * @return static<TVal, TKey>
     */
    public function only(iterable $keys)
    {
        return static::from(only($this->getItems(), $keys));
    }

    /**
     * Returns a lazy collection of items that are in $this but are not in any of the other arguments, indexed by the
     * keys from the first collection. Note that the ...$collections are iterated non-lazily.
     *
     * @param iterable<TKey, TVal> ...$collections
     * @return static<TKey, TVal>
     */
    public function diff(...$collections)
    {
        return static::from(diff($this->getItems(), ...$collections));
    }

    /**
     * Returns a lazy collection where keys and values are flipped.
     *
     * @return static<TVal, TKey>
     */
    public function flip()
    {
        return static::from(flip($this->getItems()));
    }

    /**
     * Checks for the existence of an item with$key in this collection.
     *
     * @param mixed $key
     * @return bool
     */
    public function has($key)
    {
        return has($this->getItems(), $key);
    }

    /**
     * Returns a lazy collection of non-lazy collections of items from nth position from this collection and each
     * passed collection. Stops when any of the collections don't have an item at the nth position.
     *
     * @param iterable<TKey, TVal> ...$collections
     * @return static<int, iterable<TKey, TVal>>
     */
    public function zip(iterable ...$collections)
    {
        array_unshift($collections, $this->getItems());
        return static::from(zip(...$collections));
    }

    /**
     * Uses a $transformer callable that takes a Collection and returns Collection on itself.
     *
     * @param callable $transformer Collection => Collection
     * @return CollectionInterface
     * @throws InvalidReturnValue
     */
    public function transform(callable $transformer)
    {
        $items = $this->getItems();

        $transformed = $transformer($items instanceof CollectionInterface ? $items : new Collection($items));

        if (!($transformed instanceof CollectionInterface)) {
            throw new InvalidReturnValue;
        }

        return $transformed;
    }

    /**
     * Transpose each item in a collection, interchanging the row and column indexes.
     * Can only transpose collections of collections. Otherwise an InvalidArgument is raised.
     *
     * @TODO: TVal must be iterable<X>
     * @return static<int, TVal>
     */
    public function transpose()
    {
        return static::from(function (): Generator {
            foreach (transpose($this->getItems()) as $k => $v) {
                yield $k => static::from($v);
            }
        });
    }

    /**
     * Extracts data from collection items by dot separated key path. Supports the * wildcard.  If a key contains \ or
     * it must be escaped using \ character.
     *
     * @param mixed $keyPath
     * @return CollectionInterface<mixed, mixed>
     */
    public function extract($keyPath)
    {
        return Collection::from(extract($this->getItems(), $keyPath));
    }

    /**
     * Returns a lazy collection of items that are in $this and all the other arguments, indexed by the keys from
     * the first collection. Note that the ...$collections are iterated non-lazily.
     *
     * @param iterable<TVal> ...$collections
     * @return static<TKey, TVal>
     */
    public function intersect(iterable ...$collections)
    {
        return static::from(intersect($this->getItems(), ...$collections));
    }

    /**
     * Checks whether this collection has exactly $size items.
     *
     * @param int $size
     * @return bool
     */
    public function sizeIs(int $size): bool
    {
        return sizeIs($this->getItems(), $size);
    }

    /**
     * Checks whether this collection has less than $size items.
     *
     * @param int $size
     * @return bool
     */
    public function sizeIsLessThan(int $size): bool
    {
        return sizeIsLessThan($this->getItems(), $size);
    }

    /**
     * Checks whether this collection has more than $size items.
     *
     * @param int $size
     * @return bool
     */
    public function sizeIsGreaterThan(int $size): bool
    {
        return sizeIsGreaterThan($this->getItems(), $size);
    }

    /**
     * Checks whether this collection has between $fromSize to $toSize items. $toSize can be
     * smaller than $fromSize.
     *
     * @param int $fromSize
     * @param int $toSize
     * @return bool
     */
    public function sizeIsBetween(int $fromSize, int $toSize): bool
    {
        return sizeIsBetween($this->getItems(), $fromSize, $toSize);
    }

    /**
     * Returns a sum of all values in this collection.
     *
     * @return int|float
     */
    public function sum()
    {
        return sum($this->getItems());
    }

    /**
     * Returns average of values from this collection.
     *
     * @return int|float
     */
    public function average()
    {
        return average($this->getItems());
    }

    /**
     * Returns maximal value from this collection.
     *
     * @return TVal
     */
    public function max()
    {
        return max($this->getItems());
    }

    /**
     * Returns minimal value from this collection.
     *
     * @return TVal
     */
    public function min()
    {
        return min($this->getItems());
    }

    /**
     * Returns a string by concatenating the collection values into a string.
     *
     * @return string
     */
    public function toString(): string
    {
        return toString($this->getItems());
    }

    /**
     * Returns a lazy collection with items from $collection, but items with keys  that are found in keys of
     * $replacementMap are replaced by their values.
     *
     * @param iterable<TKey, TVal> $replacementMap
     * @return static<TKey, TVal>
     */
    public function replaceByKeys(iterable $replacementMap)
    {
        return static::from(replaceByKeys($this->getItems(), $replacementMap));
    }

    /**
     * /**
     * Dumps this collection into array (recursively).
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
     * @param int|null $maxItemsPerCollection
     * @param int|null $maxDepth
     * @return array
     */
    public function dump(int $maxItemsPerCollection = null, int $maxDepth = null): array
    {
        return dump($this->getItems(), $maxItemsPerCollection, $maxDepth);
    }

    /**
     * Calls dump on this collection and then prints it using the var_export.
     *
     * @param int|null $maxItemsPerCollection
     * @param int|null $maxDepth
     * @return static
     */
    public function printDump(int $maxItemsPerCollection = null, int $maxDepth = null)
    {
        printDump($this->getItems(), $maxItemsPerCollection, $maxDepth);
        return $this;
    }

    /**
     * @return iterable<TKey, TVal>
     */
    protected abstract function getItems(): iterable;

    /**
     * @template CKey
     * @template CVal
     * @param callable():iterable<CKey, CVal>|iterable<CKey, CVal> $input
     * @return static<CKey, CVal>
     */
    protected abstract static function from($input);
}
