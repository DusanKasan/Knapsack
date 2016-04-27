<?php

namespace DusanKasan\Knapsack;

use Closure;
use DusanKasan\Knapsack\Exceptions\InvalidArgument;
use DusanKasan\Knapsack\Exceptions\ItemNotFound;
use Iterator;
use IteratorAggregate;
use RecursiveArrayIterator;
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
     * @param callable|Closure|array|Traversable $input If callable is passed, it must be a generator factory function
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
     * @param mixed $input
     * @param callable $function
     * @return Collection
     */
    public static function iterate($input, callable $function)
    {
        return iterate($input, $function);
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
        return repeat($value, $times);
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
        return \DusanKasan\Knapsack\range($start, $end, $step);
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->input->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->input->next();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->input->key();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->input->valid();
    }

    /**
     * {@inheritdoc}
     */
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
        return filter($this, $function);
    }

    /**
     * Returns a lazy collection of distinct items. The comparison is the same as in in_array.
     *
     * @return Collection
     */
    public function distinct()
    {
        return distinct($this);
    }

    /**
     * Returns a lazy collection with items from all $collections passed as argument appended together
     *
     * @param Traversable|array ...$collections
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
        return map($this, $function);
    }

    /**
     * Reduces the collection to single value by iterating over the collection and calling $function while
     * passing $startValue and current key/item as parameters. The output of $function is used as $startValue in
     * next iteration. The output of $function on last element is the return value of this function.
     *
     * @param mixed $startValue
     * @param callable $function ($tmpValue, $value, $key)
     * @return mixed
     */
    public function reduce(callable $function, $startValue)
    {
        $result = reduce($this, $function, $startValue);

        return $result;
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
        return flatten($this, $depth);
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
        return \DusanKasan\Knapsack\sort($this, $function);
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
        return slice($this, $from, $to);
    }

    /**
     * Returns collection which items are separated into groups indexed by the return value of $function.
     *
     * @param callable $function ($value, $key)
     * @return Collection
     */
    public function groupBy(callable $function)
    {
        return groupBy($this, $function);
    }

    /**
     * Returns collection which items are separated into groups indexed by the value at the given key.
     *
     * @param mixed $key The key to be used for grouping
     * @return Collection
     */
    public function groupByKey($key)
    {
        return groupByKey($this, $key);
    }

    /**
     * Returns a lazy collection in which $function is executed for each item.
     *
     * @param callable $function ($value, $key)
     * @return Collection
     */
    public function each(callable $function)
    {
        return \DusanKasan\Knapsack\each($this, $function);
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
     * Returns value at the key $key. If multiple values have this key, return first. If no value has this key, throw
     * ItemNotFound. If $convertToCollection is true and the return value is a collection (array|Traversable) an
     * instance of Collection will be returned.
     *
     * @param mixed $key
     * @param bool $convertToCollection
     * @return Collection|mixed
     * @throws ItemNotFound
     */
    public function get($key, $convertToCollection = false)
    {
        $result = get($this, $key);

        return (isCollection($result) && $convertToCollection) ? new self($result) : $result;
    }

    /**
     * Returns item at the key $key. If multiple items have this key, return first. If no item has this key, return
     * $ifNotFound. If no value has this key, throw ItemNotFound. If $convertToCollection is true and the return value
     * is a collection (array|Traversable) an instance of Collection will be returned.
     *
     * @param mixed $key
     * @param mixed $default
     * @param bool $convertToCollection
     * @return mixed
     * @throws ItemNotFound
     */
    public function getOrDefault($key, $default = null, $convertToCollection = false)
    {
        $result = getOrDefault($this, $key, $default);

        return (isCollection($result) && $convertToCollection) ? new self($result) : $result;
    }

    /**
     * Returns nth item in the collection starting from 0. If the size of this collection is smaller than $position,
     * throw ItemNotFound. If $convertToCollection is true and the return value is a collection (array|Traversable) an
     * instance of Collection will be returned.
     *
     * @param int $position
     * @param bool $convertToCollection
     * @return Collection|mixed
     * @throws ItemNotFound
     */
    public function getNth($position, $convertToCollection = false)
    {
        $result = getNth($this, $position);

        return (isCollection($result) && $convertToCollection) ? new self($result) : $result;
    }

    /**
     * Returns first value matched by $function. If no value matches, return $default. If $convertToCollection is true
     * and the return value is a collection (array|Traversable) an instance of Collection will be returned.
     *
     * @param callable $function
     * @param mixed|null $default
     * @param bool $convertToCollection
     * @return Collection|mixed
     */
    public function find(callable $function, $default = null, $convertToCollection = false)
    {
        $result = find($this, $function, $default);

        return (isCollection($result) && $convertToCollection) ? new self($result) : $result;
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
        return countBy($this, $function);
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
        return indexBy($this, $function);
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
        return reverse($this);
    }

    /**
     * Reduce the collection to single value. Walks from right to left.
     *
     * @param callable $function Must take 2 arguments, intermediate value and item from the iterator.
     * @param mixed $startValue
     * @return mixed
     */
    public function reduceRight(callable $function, $startValue)
    {
        return reduceRight($this, $function, $startValue);
    }

    /**
     * A form of slice that returns first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function take($numberOfItems)
    {
        return take($this, $numberOfItems);
    }

    /**
     * A form of slice that returns all but first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function drop($numberOfItems)
    {
        return drop($this, $numberOfItems);
    }

    /**
     * Returns collection of values from this collection but with keys being numerical from 0 upwards.
     *
     * @return Collection
     */
    public function values()
    {
        return values($this);
    }

    /**
     * Returns a lazy collection without elements matched by $function.
     *
     * @param callable $function
     * @return Collection
     */
    public function reject(callable $function)
    {
        return reject($this, $function);
    }

    /**
     * Returns a lazy collection of the keys of this collection.
     *
     * @return Collection
     */
    public function keys()
    {
        return keys($this);
    }

    /**
     * Returns a lazy collection of items of this collection separated by $separator
     *
     * @param mixed $separator
     * @return Collection
     */
    public function interpose($separator)
    {
        return interpose($this, $separator);
    }

    /**
     * Returns a lazy collection with last $numberOfItems items skipped. These are still iterated over, just skipped.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function dropLast($numberOfItems = 1)
    {
        return dropLast($this, $numberOfItems);
    }

    /**
     * Returns a lazy collection of first item from first collection, first item from second, second from first and
     * so on. Accepts any number of collections.
     *
     * @param array|Traversable ...$collections
     * @return Collection
     */
    public function interleave(...$collections)
    {
        return interleave($this, ...$collections);
    }

    /**
     * Returns an infinite lazy collection of items in this collection repeated infinitely.
     *
     * @return Collection
     */
    public function cycle()
    {
        return cycle($this);
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
        return prepend($this, $value, $key);
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
        return append($this, $value, $key);
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
        return dropWhile($this, $function);
    }

    /**
     * Returns a lazy collection which is a result of calling map($function) and then flatten(1)
     *
     * @param callable $function
     * @return Collection
     */
    public function mapcat(callable $function)
    {
        return mapcat($this, $function);
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
        return takeWhile($this, $function);
    }

    /**
     * Returns a collection of [take($position), drop($position)]
     *
     * @param int $position
     * @return Collection
     */
    public function splitAt($position)
    {
        return splitAt($this, $position);
    }

    /**
     * Returns a collection of [takeWhile($predicament), dropWhile($predicament]
     *
     * @param callable $function
     * @return Collection
     */
    public function splitWith(callable $function)
    {
        return splitWith($this, $function);
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
        return replace($this, $replacementMap);
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
        return reductions($this, $function, $startValue);
    }

    /**
     * Returns a lazy collection of every nth item in this collection
     *
     * @param int $step
     * @return Collection
     */
    public function takeNth($step)
    {
        return takeNth($this, $step);
    }

    /**
     * Returns a non-collection of shuffled items from this collection
     *
     * @return Collection
     */
    public function shuffle()
    {
        return shuffle($this);
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
        return partition($this, $numberOfItems, $step, $padding);
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
        return partitionBy($this, $function);
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
        return frequencies($this);
    }

    /**
     * Returns first item of this collection. If the collection is empty, throws ItemNotFound. If $convertToCollection
     * is true and the return value is a collection (array|Traversable) an instance of Collection is returned.
     *
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws ItemNotFound
     */
    public function first($convertToCollection = false)
    {
        $result = first($this);
        return (isCollection($result) && $convertToCollection) ? new self($result) : $result;
    }

    /**
     * Returns last item of this collection. If the collection is empty, throws ItemNotFound. If $convertToCollection
     * is true and the return value is a collection (array|Traversable) an
     *
     * @param bool $convertToCollection
     * @return mixed|Collection
     * @throws ItemNotFound
     */
    public function last($convertToCollection = false)
    {
        $result = last($this);
        return (isCollection($result) && $convertToCollection) ? new self($result) : $result;
    }

    /**
     * Returns a lazy collection by picking a $key key from each sub-collection of $this.
     *
     * @param mixed $key
     * @return Collection
     */
    public function pluck($key)
    {
        return pluck($this, $key);
    }

    /**
     * Realizes collection - turns lazy collection into non-lazy one by iterating over it and storing the key/values.
     *
     * @return Collection
     */
    public function realize()
    {
        return realize($this);
    }
}
