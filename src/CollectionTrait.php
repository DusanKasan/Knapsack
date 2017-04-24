<?php

namespace DusanKasan\Knapsack;

use DusanKasan\Knapsack\Exceptions\InvalidReturnValue;

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
     * @param callable|null $function ($value, $key)
     * @return Collection
     */
    public function filter(callable $function = null)
    {
        return filter($this->getItems(), $function);
    }

    /**
     * Returns a lazy collection of distinct items. The comparison is the same as in in_array.
     *
     * @return Collection
     */
    public function distinct()
    {
        return distinct($this->getItems());
    }

    /**
     * Returns a lazy collection with items from all $collections passed as argument appended together
     *
     * @param array|\Traversable ...$collections
     * @return Collection
     */
    public function concat(...$collections)
    {
        return concat($this, ...$collections);
    }

    /**
     * Returns collection where each item is changed to the output of executing $function on each key/item.
     *
     * @param callable $function
     * @return Collection
     */
    public function map(callable $function)
    {
        return map($this->getItems(), $function);
    }

    /**
     * Reduces the collection to single value by iterating over the collection and calling $function while
     * passing $startValue and current key/item as parameters. The output of $function is used as $startValue in
     * next iteration. The output of $function on last element is the return value of this function. If
     * $convertToCollection is true and the return value is a collection (array|Traversable) an instance of Collection
     * is returned.
     *
     * @param callable $function ($tmpValue, $value, $key)
     * @param mixed $startValue
     * @param bool $convertToCollection
     * @return mixed|Collection
     */
    public function reduce(callable $function, $startValue, $convertToCollection = false)
    {
        $result = reduce($this->getItems(), $function, $startValue);

        return ($convertToCollection && isCollection($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns a lazy collection with one or multiple levels of nesting flattened. Removes all nesting when no value
     * is passed.
     *
     * @param int $depth How many levels should be flatten, default (-1) is infinite.
     * @return Collection
     */
    public function flatten($depth = -1)
    {
        return flatten($this->getItems(), $depth);
    }

    /**
     * Returns a non-lazy collection sorted using $function($item1, $item2, $key1, $key2 ). $function should
     * return true if first item is larger than the second and false otherwise.
     *
     * @param callable $function ($value1, $value2, $key1, $key2)
     * @return Collection
     */
    public function sort(callable $function)
    {
        return \DusanKasan\Knapsack\sort($this->getItems(), $function);
    }

    /**
     * Returns lazy collection items of which are part of the original collection from item number $from to item
     * number $to. The items before $from are also iterated over, just not returned.
     *
     * @param int $from
     * @param int $to If omitted, will slice until end
     * @return Collection
     */
    public function slice($from, $to = -1)
    {
        return slice($this->getItems(), $from, $to);
    }

    /**
     * Returns collection which items are separated into groups indexed by the return value of $function.
     *
     * @param callable $function ($value, $key)
     * @return Collection
     */
    public function groupBy(callable $function)
    {
        return groupBy($this->getItems(), $function);
    }

    /**
     * Returns collection where items are separated into groups indexed by the value at given key.
     *
     * @param mixed $key
     * @return Collection
     */
    public function groupByKey($key)
    {
        return groupByKey($this->getItems(), $key);
    }

    /**
     * Returns a lazy collection in which $function is executed for each item.
     *
     * @param callable $function ($value, $key)
     * @return Collection
     */
    public function each(callable $function)
    {
        return \DusanKasan\Knapsack\each($this->getItems(), $function);
    }

    /**
     * Returns the number of items in this collection.
     *
     * @return int
     */
    public function size()
    {
        return size($this->getItems());
    }

    /**
     * Returns value at the key $key. If multiple values have this key, return first. If no value has this key, throw
     * ItemNotFound. If $convertToCollection is true and the return value is a collection (array|Traversable) an
     * instance of Collection will be returned.
     *
     * @param mixed $key
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws \DusanKasan\Knapsack\Exceptions\ItemNotFound
     */
    public function get($key, $convertToCollection = false)
    {
        $result = get($this->getItems(), $key);

        return ($convertToCollection && isCollection($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns item at the key $key. If multiple items have this key, return first. If no item has this key, return
     * $ifNotFound. If no value has this key, throw ItemNotFound. If $convertToCollection is true and the return value
     * is a collection (array|Traversable) an instance of Collection will be returned.
     *
     * @param mixed $key
     * @param mixed $default
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws \DusanKasan\Knapsack\Exceptions\ItemNotFound
     */
    public function getOrDefault($key, $default = null, $convertToCollection = false)
    {
        $result = getOrDefault($this->getItems(), $key, $default);

        return ($convertToCollection && isCollection($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns first value matched by $function. If no value matches, return $default. If $convertToCollection is true
     * and the return value is a collection (array|Traversable) an instance of Collection will be returned.
     *
     * @param callable $function
     * @param mixed|null $default
     * @param bool $convertToCollection
     * @return mixed|Collection
     */
    public function find(callable $function, $default = null, $convertToCollection = false)
    {
        $result = find($this->getItems(), $function, $default);

        return ($convertToCollection && isCollection($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns a non-lazy collection of items whose keys are the return values of $function and values are the number of
     * items in this collection for which the $function returned this value.
     *
     * @param callable $function
     * @return Collection
     */
    public function countBy(callable $function)
    {
        return countBy($this->getItems(), $function);
    }

    /**
     * Returns a lazy collection by changing keys of this collection for each item to the result of $function for
     * that item.
     *
     * @param callable $function
     * @return Collection
     */
    public function indexBy(callable $function)
    {
        return indexBy($this->getItems(), $function);
    }

    /**
     * Returns true if $function returns true for every item in this collection, false otherwise.
     *
     * @param callable $function
     * @return bool
     */
    public function every(callable $function)
    {
        return every($this->getItems(), $function);
    }

    /**
     * Returns true if $function returns true for at least one item in this collection, false otherwise.
     *
     * @param callable $function
     * @return bool
     */
    public function some(callable $function)
    {
        return some($this->getItems(), $function);
    }

    /**
     * Returns true if $value is present in the collection.
     *
     * @param mixed $value
     * @return bool
     */
    public function contains($value)
    {
        return contains($this->getItems(), $value);
    }

    /**
     * Returns collection of items in this collection in reverse order.
     *
     * @return Collection
     */
    public function reverse()
    {
        return reverse($this->getItems());
    }

    /**
     * Reduce the collection to single value. Walks from right to left. If $convertToCollection is true and the return
     * value is a collection (array|Traversable) an instance of Collection is returned.
     *
     * @param callable $function Must take 2 arguments, intermediate value and item from the iterator.
     * @param mixed $startValue
     * @param bool $convertToCollection
     * @return mixed|Collection
     */
    public function reduceRight(callable $function, $startValue, $convertToCollection = false)
    {
        $result = reduceRight($this->getItems(), $function, $startValue);

        return ($convertToCollection && isCollection($result)) ? new Collection($result) : $result;
    }

    /**
     * A form of slice that returns first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function take($numberOfItems)
    {
        return take($this->getItems(), $numberOfItems);
    }

    /**
     * A form of slice that returns all but first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function drop($numberOfItems)
    {
        return drop($this->getItems(), $numberOfItems);
    }

    /**
     * Returns collection of values from this collection but with keys being numerical from 0 upwards.
     *
     * @return Collection
     */
    public function values()
    {
        return values($this->getItems());
    }

    /**
     * Returns a lazy collection without elements matched by $function.
     *
     * @param callable $function
     * @return Collection
     */
    public function reject(callable $function)
    {
        return reject($this->getItems(), $function);
    }

    /**
     * Returns a lazy collection of the keys of this collection.
     *
     * @return Collection
     */
    public function keys()
    {
        return keys($this->getItems());
    }

    /**
     * Returns a lazy collection of items of this collection separated by $separator
     *
     * @param mixed $separator
     * @return Collection
     */
    public function interpose($separator)
    {
        return interpose($this->getItems(), $separator);
    }

    /**
     * Returns a lazy collection with last $numberOfItems items skipped. These are still iterated over, just skipped.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function dropLast($numberOfItems = 1)
    {
        return dropLast($this->getItems(), $numberOfItems);
    }

    /**
     * Returns a lazy collection of first item from first collection, first item from second, second from first and
     * so on. Accepts any number of collections.
     *
     * @param array|\Traversable ...$collections
     * @return Collection
     */
    public function interleave(...$collections)
    {
        return interleave($this->getItems(), ...$collections);
    }

    /**
     * Returns an infinite lazy collection of items in this collection repeated infinitely.
     *
     * @return Collection
     */
    public function cycle()
    {
        return cycle($this->getItems());
    }

    /**
     * Returns a lazy collection of items of this collection with $value added as first element. If $key is not provided
     * it will be next integer index.
     *
     * @param mixed $value
     * @param mixed|null $key
     * @return Collection
     */
    public function prepend($value, $key = null)
    {
        return prepend($this->getItems(), $value, $key);
    }

    /**
     * Returns a lazy collection of items of this collection with $value added as last element. If $key is not provided
     * it will be next integer index.
     *
     * @param mixed $value
     * @param mixed $key
     * @return Collection
     */
    public function append($value, $key = null)
    {
        return append($this->getItems(), $value, $key);
    }

    /**
     * Returns a lazy collection by removing items from this collection until first item for which $function returns
     * false.
     *
     * @param callable $function
     * @return Collection
     */
    public function dropWhile(callable $function)
    {
        return dropWhile($this->getItems(), $function);
    }

    /**
     * Returns a lazy collection which is a result of calling map($function) and then flatten(1)
     *
     * @param callable $function
     * @return Collection
     */
    public function mapcat(callable $function)
    {
        return mapcat($this->getItems(), $function);
    }

    /**
     * Returns a lazy collection of items from the start of the ollection until the first item for which $function
     * returns false.
     *
     * @param callable $function
     * @return Collection
     */
    public function takeWhile(callable $function)
    {
        return takeWhile($this->getItems(), $function);
    }

    /**
     * Returns a collection of [take($position), drop($position)]
     *
     * @param int $position
     * @return Collection
     */
    public function splitAt($position)
    {
        return splitAt($this->getItems(), $position);
    }

    /**
     * Returns a collection of [takeWhile($predicament), dropWhile($predicament]
     *
     * @param callable $function
     * @return Collection
     */
    public function splitWith(callable $function)
    {
        return splitWith($this->getItems(), $function);
    }

    /**
     * Returns a lazy collection with items from this collection but values that are found in keys of $replacementMap
     * are replaced by their values.
     *
     * @param array|\Traversable $replacementMap
     * @return Collection
     */
    public function replace($replacementMap)
    {
        return replace($this->getItems(), $replacementMap);
    }

    /**
     * Returns a lazy collection of reduction steps.
     *
     * @param callable $function
     * @param mixed $startValue
     * @return Collection
     */
    public function reductions(callable $function, $startValue)
    {
        return reductions($this->getItems(), $function, $startValue);
    }

    /**
     * Returns a lazy collection of every nth item in this collection
     *
     * @param int $step
     * @return Collection
     */
    public function takeNth($step)
    {
        return takeNth($this->getItems(), $step);
    }

    /**
     * Returns a non-collection of shuffled items from this collection
     *
     * @return Collection
     */
    public function shuffle()
    {
        return \DusanKasan\Knapsack\shuffle($this->getItems());
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
     * @param array|\Traversable $padding
     * @return Collection
     */
    public function partition($numberOfItems, $step = 0, $padding = [])
    {
        return partition($this->getItems(), $numberOfItems, $step, $padding);
    }

    /**
     * Creates a lazy collection of collections created by partitioning this collection every time $function will
     * return different result.
     *
     * @param callable $function
     * @return Collection
     */
    public function partitionBy(callable $function)
    {
        return partitionBy($this->getItems(), $function);
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
     * @return Collection
     */
    public function frequencies()
    {
        return frequencies($this->getItems());
    }

    /**
     * Returns first item of this collection. If the collection is empty, throws ItemNotFound. If $convertToCollection
     * is true and the return value is a collection (array|Traversable) an instance of Collection is returned.
     *
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws \DusanKasan\Knapsack\Exceptions\ItemNotFound
     */
    public function first($convertToCollection = false)
    {
        $result = first($this->getItems());

        return ($convertToCollection && isCollection($result)) ? new Collection($result) : $result;
    }

    /**
     * Returns last item of this collection. If the collection is empty, throws ItemNotFound. If $convertToCollection
     * is true and the return value is a collection (array|Traversable) it is converted to Collection.
     *
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws \DusanKasan\Knapsack\Exceptions\ItemNotFound
     */
    public function last($convertToCollection = false)
    {
        $result = last($this->getItems());

        return ($convertToCollection && isCollection($result)) ? new Collection($result) : $result;
    }

    /**
     * Realizes collection - turns lazy collection into non-lazy one by iterating over it and storing the key/values.
     *
     * @return Collection
     */
    public function realize()
    {
        return realize($this->getItems());
    }

    /**
     * Returns the second item in this collection or throws ItemNotFound if the collection is empty or has 1 item. If
     * $convertToCollection is true and the return value is a collection (array|Traversable) it is converted to
     * Collection.
     *
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws \DusanKasan\Knapsack\Exceptions\ItemNotFound
     */
    public function second($convertToCollection = false)
    {
        $result = second($this->getItems());

        return ($convertToCollection && isCollection($result)) ? new Collection($result) : $result;
    }

    /**
     * Combines the values of this collection as keys, with values of $collection as values.  The resulting collection
     * has length equal to the size of smaller collection.
     *
     * @param array|\Traversable $collection
     * @return Collection
     * @throws \DusanKasan\Knapsack\Exceptions\ItemNotFound
     */
    public function combine($collection)
    {
        return combine($this->getItems(), $collection);
    }

    /**
     * Returns a lazy collection without the items associated to any of the keys from $keys.
     *
     * @param array|\Traversable $keys
     * @return Collection
     */
    public function except($keys)
    {
        return except($this->getItems(), $keys);
    }

    /**
     * Returns a lazy collection of items associated to any of the keys from $keys.
     *
     * @param array|\Traversable $keys
     * @return Collection
     */
    public function only($keys)
    {
        return only($this->getItems(), $keys);
    }

    /**
     * Returns a lazy collection of items that are in $this but are not in any of the other arguments, indexed by the
     * keys from the first collection. Note that the ...$collections are iterated non-lazily.
     *
     * @param array|\Traversable ...$collections
     * @return Collection
     */
    public function diff(...$collections)
    {
        return diff($this->getItems(), ...$collections);
    }

    /**
     * Returns a lazy collection where keys and values are flipped.
     *
     * @return Collection
     */
    public function flip()
    {
        return flip($this->getItems());
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
     * @param array|\Traversable ...$collections
     * @return Collection
     */
    public function zip(...$collections)
    {
        array_unshift($collections, $this->getItems());

        return zip(...$collections);
    }

    /**
     * Uses a $transformer callable that takes a Collection and returns Collection on itself.
     *
     * @param callable $transformer Collection => Collection
     * @return Collection
     * @throws InvalidReturnValue
     */
    public function transform(callable $transformer)
    {
        $items = $this->getItems();

        $transformed = $transformer($items instanceof Collection ? $items : new Collection($items));

        if (!($transformed instanceof Collection)) {
            throw new InvalidReturnValue;
        }

        return $transformed;
    }

    /**
     * Transpose each item in a collection, interchanging the row and column indexes.
     * Can only transpose collections of collections. Otherwise an InvalidArgument is raised.
     *
     * @return Collection
     */
    public function transpose()
    {
        return transpose($this->getItems());
    }

    /**
     * Extracts data from collection items by dot separated key path. Supports the * wildcard.  If a key contains \ or
     * it must be escaped using \ character.
     *
     * @param mixed $keyPath
     * @return Collection
     */
    public function extract($keyPath)
    {
        return \DusanKasan\Knapsack\extract($this->getItems(), $keyPath);
    }

    /**
     * Returns a lazy collection of items that are in $this and all the other arguments, indexed by the keys from
     * the first collection. Note that the ...$collections are iterated non-lazily.
     *
     * @param array|\Traversable ...$collections
     * @return Collection
     */
    public function intersect(...$collections)
    {
        return intersect($this->getItems(), ...$collections);
    }

    /**
     * Checks whether this collection has exactly $size items.
     *
     * @param int $size
     * @return bool
     */
    public function sizeIs($size)
    {
        return sizeIs($this->getItems(), $size);
    }

    /**
     * Checks whether this collection has less than $size items.
     *
     * @param int $size
     * @return bool
     */
    public function sizeIsLessThan($size)
    {
        return sizeIsLessThan($this->getItems(), $size);
    }

    /**
     * Checks whether this collection has more than $size items.
     *
     * @param int $size
     * @return bool
     */
    public function sizeIsGreaterThan($size)
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
    public function sizeIsBetween($fromSize, $toSize)
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
     * @return mixed
     */
    public function max()
    {
        return \DusanKasan\Knapsack\max($this->getItems());
    }

    /**
     * Returns minimal value from this collection.
     *
     * @return mixed
     */
    public function min()
    {
        return \DusanKasan\Knapsack\min($this->getItems());
    }

    /**
     * Returns a string by concatenating the collection values into a string.
     *
     * @return string
     */
    public function toString()
    {
        return toString($this->getItems());
    }

    /**
     * Returns a lazy collection with items from $collection, but items with keys  that are found in keys of
     * $replacementMap are replaced by their values.
     *
     * @param array|\Traversable $replacementMap
     * @return Collection
     */
    public function replaceByKeys($replacementMap)
    {
        return replaceByKeys($this->getItems(), $replacementMap);
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
    public function dump($maxItemsPerCollection = null, $maxDepth = null)
    {
        return dump($this->getItems(), $maxItemsPerCollection, $maxDepth);
    }

    /**
     * Calls dump on this collection and then prints it using the var_export.
     *
     * @param int|null $maxItemsPerCollection
     * @param int|null $maxDepth
     * @return Collection
     */
    public function printDump($maxItemsPerCollection = null, $maxDepth = null)
    {
        return printDump($this->getItems(), $maxItemsPerCollection, $maxDepth);
    }

    /**
     * @return array|\Traversable
     */
    protected function getItems()
    {
        return $this;
    }
}
