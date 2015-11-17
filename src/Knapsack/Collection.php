<?php

namespace Knapsack;

use Iterator;
use IteratorAggregate;
use Knapsack\Callback\Argument;
use Knapsack\Callback\Callback;
use Knapsack\Exceptions\InvalidArgument;
use Knapsack\Exceptions\ItemNotFound;
use RecursiveArrayIterator;
use stdClass;
use Traversable;

class Collection implements Iterator
{
    /**
     * @var Iterator
     */
    protected $input;

    /**
     * @var callable
     */
    private $generatorFactory;

    /**
     * @param callable|array|Traversable $input If callable is passed, it must be a generator factory function
     */
    public function __construct($input)
    {
        if (is_array($input)) {
            $input = new RecursiveArrayIterator($input);
            $this->input = $input;
        } elseif ($input instanceof IteratorAggregate) {
            $input = $input->getIterator();
            $this->input = $input;
        } elseif (is_callable($input)) {
            $this->generatorFactory = $input;
            $this->input = $input();
        } elseif ($input instanceof Iterator) {
            $this->input = $input;
        } else {
            throw new InvalidArgument;
        }

        $this->rewind();
    }

    /**
     * Static alias of normal constructor.
     *
     * @param array|Traversable $input
     * @return Collection
     */
    public static function from($input)
    {
        return new self($input);
    }

    /**
     * Returns lazy collection of values, where first value is $input and all subsequent values are computed by applying
     * $function to the last value in the collection. By default this produces an infinite collection. However you can
     * end the collection by throwing a NoMoreItems exception.
     *
     * @param array|Traversable $input
     * @param callable $function
     * @return Collection
     */
    public static function iterate($input, callable $function)
    {
        $generatorFactory = function () use ($input, $function) {
            return iterate($input, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection of $value repeated $times times. If $times is not provided the collection is infinite.
     *
     * @param mixed $value
     * @param int $times
     * @return Collection
     */
    public static function repeat($value, $times = -1)
    {
        $generatorFactory = function () use ($value, $times) {
            return repeat($value, $times);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection of numbers starting at $start, incremented by $step until $end is reached.
     *
     * @param int $start
     * @param int|null $end
     * @param int $step
     * @return Collection
     */
    public static function range($start = 0, $end = null, $step = 1)
    {
        $generatorFactory = function () use ($start, $end, $step) {
            return \Knapsack\range($start, $end, $step);
        };

        return new self($generatorFactory);
    }

    public function current()
    {
        return $this->input->current();
    }

    public function next()
    {
        $this->input->next();
    }

    public function key()
    {
        return $this->input->key();
    }

    public function valid()
    {
        return $this->input->valid();
    }

    public function rewind()
    {
        if ($this->generatorFactory) {
            $this->input = call_user_func($this->generatorFactory);
        }

        $this->input->rewind();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return toArray($this);
    }

    /**
     * Returns a lazy collection of items for which $function returned true.
     *
     * @param callable $function ($value, $key)
     * @return Collection
     */
    public function filter(callable $function)
    {
        $generatorFactory = function () use ($function) {
            return filter($this, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection of distinct items. The comparison is the same as in in_array.
     *
     * @return Collection
     */
    public function distinct()
    {
        $generatorFactory = function () {
            return distinct($this);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection with items from $this and $collection.
     *
     * @param Traversable|array $collection
     * @return Collection
     */
    public function concat($collection)
    {
        $generatorFactory = function () use ($collection) {
            return concat($this, $collection);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns collection where each item is changed to the output of executing $function on each key/item.
     *
     * @param callable $function
     * @return Collection
     */
    public function map(callable $function)
    {
        $generatorFactory = function () use ($function) {
            return map($this, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Reduces the collection to single value by iterating over the collection and calling $function while
     * passing $startValue and current key/item as parameters. The output of $function is used as $startValue in
     * next iteration. The output of $function on last element is the return value of this function. If the return
     * value is a collection (array|Traversable) an instance of Collection will be returned.
     *
     * @param mixed $startValue
     * @param callable $function ($tmpValue, $value, $key)
     * @return mixed|Collection
     */
    public function reduce(callable $function, $startValue)
    {
        $result = reduce($this, $function, $startValue);

        return isCollection($result) ? new self($result) : $result;
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
        $generatorFactory = function () use ($depth) {
            return flatten($this, $depth);
        };

        return new self($generatorFactory);
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
        $generatorFactory = function () use ($function) {
            return \Knapsack\sort($this, $function);
        };

        return new self($generatorFactory);
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
        $generatorFactory = function () use ($from, $to) {
            return slice($this, $from, $to);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns collection which items are separated into groups indexed by the return value of $function.
     *
     * @param callable $function ($value, $key)
     * @return Collection
     */
    public function groupBy(callable $function)
    {
        return new self(groupBy($this, $function));
    }

    /**
     * Returns a lazy collection in which $function is executed for each item.
     *
     * @param callable $function ($value, $key)
     * @return Collection
     */
    public function each(callable $function)
    {
        $generatorFactory = function () use ($function) {
            return \Knapsack\each($this, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns the number of items in this collection.
     *
     * @return int
     */
    public function size()
    {
        return size($this);
    }

    /**
     * Returns value at the key $key. If multiple values have this key, return first. If no value has
     * this key, throw ItemNotFound.If the return value is a collection (array|Traversable) an instance of Collection
     * will be returned.
     *
     * @param mixed $key
     * @return mixed|Collection
     */
    public function get($key)
    {
        $result = get($this, $key);

        return isCollection($result) ? new self($result) : $result;
    }

    /**
     * Returns item at the key $key. If multiple items have this key, return first. If no item has
     * this key, return $ifNotFound. If the return value is a collection (array|Traversable) an instance of Collection
     * will be returned.
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed|Collection
     */
    public function getOrDefault($key, $default = null)
    {
        $result = getOrDefault($this, $key, $default);

        return isCollection($result) ? new self($result) : $result;
    }

    /**
     * Returns nth item in the collection starting from 0. If the size of this collection is smaller than $position,
     * throw ItemNotFound. If the return value is a collection (array|Traversable) an instance of Collection
     * will be returned.
     *
     * @param int $position
     * @return mixed|Collection
     */
    public function getNth($position)
    {
        $result = getNth($this, $position);

        return isCollection($result) ? new self($result) : $result;
    }

    /**
     * Returns first value matched by $function. If no value matches, return $default. If the return value is a
     * collection (array|Traversable) an instance of Collection will be returned.
     *
     * @param callable $function
     * @param mixed|null $default
     * @return mixed|Collection
     */
    public function find(callable $function, $default = null)
    {
        $result = find($this, $function, $default);

        return isCollection($result) ? new self($result) : $result;
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
        $generatorFactory = function () use ($function) {
            return countBy($this, $function);
        };

        return new self($generatorFactory);
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
        $generatorFactory = function () use ($function) {
            return indexBy($this, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns true if $function returns true for every item in this collection, false otherwise.
     *
     * @param callable $function
     * @return bool
     */
    public function every(callable $function)
    {
        return every($this, $function);
    }

    /**
     * Returns true if $function returns true for at least one item in this collection, false otherwise.
     *
     * @param callable $function
     * @return bool
     */
    public function some(callable $function)
    {
        return some($this, $function);
    }

    /**
     * Returns true if $value is present in the collection.
     *
     * @param mixed $value
     * @return bool
     */
    public function contains($value)
    {
        return contains($this, $value);
    }

    /**
     * Returns collection of items in this collection in reverse order.
     *
     * @return Collection
     */
    public function reverse()
    {
        $generatorFactory = function () {
            return reverse($this);
        };

        return new self($generatorFactory);
    }

    /**
     * Reduce the collection to single value. Walks from right to left.
     *
     * @param callable $function Must take 2 arguments, intermediate value and item from the iterator.
     * @param mixed $startValue
     * @return mixed|Collection
     */
    public function reduceRight(callable $function, $startValue)
    {
        $result = reduceRight($this, $function, $startValue);

        return isCollection($result) ? new self($result) : $result;
    }

    /**
     * A form of slice that returns first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function take($numberOfItems)
    {
        $generatorFactory = function () use ($numberOfItems) {
            return take($this, $numberOfItems);
        };

        return new self($generatorFactory);
    }

    /**
     * A form of slice that returns all but first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function drop($numberOfItems)
    {
        $generatorFactory = function () use ($numberOfItems) {
            return drop($this, $numberOfItems);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns collection of values from this collection but with keys being numerical from 0 upwards.
     *
     * @return Collection
     */
    public function values()
    {
        $generatorFactory = function () {
            return values($this);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection without elements matched by $function.
     *
     * @param callable $function
     * @return Collection
     */
    public function reject(callable $function)
    {
        $generatorFactory = function () use ($function) {
            return reject($this, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection of the keys of this collection.
     *
     * @return Collection
     */
    public function keys()
    {
        $generatorFactory = function () {
            return keys($this);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection of items of this collection separated by $separator
     *
     * @param mixed $separator
     * @return Collection
     */
    public function interpose($separator)
    {
        $generatorFactory = function () use ($separator) {
            return interpose($this, $separator);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection with last $numberOfItems items skipped. These are still iterated over, just skipped.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function dropLast($numberOfItems = 1)
    {
        $generatorFactory = function () use ($numberOfItems) {
            return dropLast($this, $numberOfItems);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection of first item from first collection, first item from second, second from first and
     * so on.
     *
     * @param Traversable|array $collection
     * @return Collection
     */
    public function interleave($collection)
    {
        $generatorFactory = function () use ($collection) {
            return interleave($this, $collection);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns an infinite lazy collection of items in this collection repeated infinitely.
     *
     * @return Collection
     */
    public function cycle()
    {
        $generatorFactory = function () {
            return cycle($this);
        };

        return new self($generatorFactory);
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
        $generatorFactory = function () use ($value, $key) {
            return prepend($this, $value, $key);
        };

        return new self($generatorFactory);
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
        $generatorFactory = function () use ($value, $key) {
            return append($this, $value, $key);
        };

        return new self($generatorFactory);
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
        $generatorFactory = function () use ($function) {
            return dropWhile($this, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection which is a result of calling map($function) and then flatten(1)
     *
     * @param callable $function
     * @return Collection
     */
    public function mapcat(callable $function)
    {
        $generatorFactory = function () use ($function) {
            return mapcat($this, $function);
        };

        return new self($generatorFactory);
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
        $generatorFactory = function () use ($function) {
            return takeWhile($this, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a collection of [take($position), drop($position)]
     *
     * @param int $position
     * @return Collection
     */
    public function splitAt($position)
    {
        $generatorFactory = function () use ($position) {
            return splitAt($this, $position);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a collection of [takeWhile($predicament), dropWhile($predicament]
     *
     * @param callable $function
     * @return Collection
     */
    public function splitWith(callable $function)
    {
        $generatorFactory = function () use ($function) {
            return splitWith($this, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection with items from this collection but values that are found in keys of $replacementMap
     * are replaced by their values.
     *
     * @param Traversable|array $replacementMap
     * @return Collection
     */
    public function replace($replacementMap)
    {
        $generatorFactory = function () use ($replacementMap) {
            return replace($this, $replacementMap);
        };

        return new self($generatorFactory);
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
        $generatorFactory = function () use ($function, $startValue) {
            return reductions($this, $function, $startValue);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a lazy collection of every nth item in this collection
     *
     * @param int $step
     * @return Collection
     */
    public function takeNth($step)
    {
        $generatorFactory = function () use ($step) {
            return takeNth($this, $step);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns a non-collection of shuffled items from this collection
     *
     * @return Collection
     */
    public function shuffle()
    {
        $generatorFactory = function () {
            return shuffle($this);
        };

        return new self($generatorFactory);
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
     * @param array $padding
     * @return Collection
     */
    public function partition($numberOfItems, $step = 0, $padding = [])
    {
        $generatorFactory = function () use ($numberOfItems, $step, $padding) {
            return partition($this, $numberOfItems, $step, $padding);
        };

        return new self($generatorFactory);
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
        $generatorFactory = function () use ($function) {
            return partitionBy($this, $function);
        };

        return new self($generatorFactory);
    }

    /**
     * Returns true if this collection is empty. False otherwise.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return isEmpty($this);
    }

    /**
     * Opposite of isEmpty.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return isNotEmpty($this);
    }

    /**
     * Returns a collection where keys are distinct items from this collection and their values are number of
     * occurrences of each value.
     *
     * @return Collection
     */
    public function frequencies()
    {
        return new self(frequencies($this));
    }

    /**
     * Returns first item of this collection. If the collection is empty, throws exception
     *
     * @throws ItemNotFound
     * @return mixed|Collection
     */
    public function first()
    {
        $result = first($this);
        return isCollection($result) ? new self($result) : $result;
    }

    /**
     * Returns last item of this collection. If the collection is empty, throws exception
     *
     * @throws ItemNotFound
     * @return mixed|Collection
     */
    public function last()
    {
        $result = last($this);
        return isCollection($result) ? new self($result) : $result;
    }

    /**
     * Returns a lazy collection by picking a $key key from each sub-collection of $collection.
     *
     * @param mixed $key
     * @return Collection
     */
    public function pluck($key)
    {
        return new self(pluck($this, $key));
    }
}
