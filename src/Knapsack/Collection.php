<?php

namespace Knapsack;

use Iterator;
use IteratorAggregate;
use Knapsack\Exceptions\InvalidArgument;
use RecursiveArrayIterator;
use ReflectionFunction;
use stdClass;
use Traversable;

class Collection implements Iterator
{
    /**
     * @var Iterator
     */
    protected $input;

    /**
     * @param array|Traversable $input
     */
    public function __construct($input)
    {
        if (is_array($input)) {
            $input = new RecursiveArrayIterator($input);
        } elseif ($input instanceof IteratorAggregate) {
            $input = $input->getIterator();
        } elseif (!($input instanceof Iterator)) {
            throw new InvalidArgument;
        }

        $input->rewind();
        $this->input = $input;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $toArrayRecursive = function ($item) use (&$toArrayRecursive) {
            $result = $item;
            if ($item instanceof Traversable) {
                $result = [];
                foreach ($item as $key => $value) {
                    $result[$key] = $toArrayRecursive($value);
                }
            }

            return $result;
        };

        return $toArrayRecursive($this);
    }

    public function current()
    {
        $current = $this->input->current();

        return $current;
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
        $this->input->rewind();
    }

    /**
     * Returns a lazy collection of items for which $filter returned true.
     *
     * @param callable $filter
     * @return Collection
     */
    public function filter(callable $filter)
    {
        return new FilteredCollection($this, $filter);
    }

    /**
     * Returns a lazy collection of distinct items. The comparison whether the item is in the collection or not is the same as in in_array.
     *
     * @return Collection
     */
    public function distinct()
    {
        return new DistinctCollection($this);
    }

    /**
     * Returns a lazy collection with items from $this and $collection.
     *
     * @param Traversable|array $collection
     * @return Collection
     */
    public function concat($collection)
    {
        return new ConcatenatedCollection($this, $collection);
    }

    /**
     * Returns collection where each key/item is changed to the output of executing $mapping on each key/item. If you wish to modify keys, yield 2 values in $mapping. First is key, second is item.
     *
     * @param callable $mapping
     * @return Collection
     */
    public function map(callable $mapping)
    {
        return new MappedCollection($this, $mapping);
    }

    /**
     * Reduces the collection to single value by iterating over the collection and calling $reduction while passing $startValue and current key/item as parameters. The output of $reduction is used as $startValue in next iteration. The output of $reduction on last element is the return value of this function.
     *
     * @param mixed $startValue
     * @param callable $reduction Must take 2 arguments, intermediate value and item from the iterator.
     * @return mixed
     */
    public function reduce($startValue, callable $reduction)
    {
        $usesKeys = $this->getNumberOfArguments($reduction) == 3;

        foreach ($this as $key => $item) {
            $startValue = $usesKeys ? $reduction($startValue, $key, $item) : $reduction($startValue, $item);
        }

        return $startValue;
    }

    /**
     * Returns a lazy collection with one or multiple levels of nesting flattened. Removes all nesting when no value is passed.
     *
     * @param int $depth How many levels should be flatten, default (-1) is infinite.
     * @return Collection
     */
    public function flatten($depth = -1)
    {
        return new FlattenedCollection($this, $depth);
    }

    /**
     * Returns collection sorted using $sortCallback($item1, $item2). $sortCallback should return true if first item is larger than the second and false otherwise.
     *
     * @param callable $sortCallback
     * @return Collection
     */
    public function sort(callable $sortCallback)
    {
        return new SortedCollection($this, $sortCallback);
    }

    /**
     * Returns lazy collection items of which are part of the original collection from item number $from to item number $to inclusive. The items before $from are also iterated over, just not returned.
     *
     * @param int $from
     * @param int $to If omitted, will slice until end
     * @return Collection
     */
    public function slice($from, $to = 0)
    {
        return new SlicedCollection($this, $from, $to);
    }

    /**
     * Returns collection which items are separated into groups indexed by the return value of $grouping.
     *
     * @param callable $differentiator
     * @return Collection
     */
    public function groupBy(callable $differentiator)
    {
        return new GroupedCollection($this, $differentiator);
    }

    /**
     * Returns a lazy collection in which ca$callback is executed for each item. $callback could take 1 argument (the item) or 2 arguments (key, item).
     *
     * @param callable $callback
     * @return Collection
     */
    public function each(callable $callback)
    {
        return new ForEachCollection($this, $callback);
    }

    /**
     * Returns the number of items in this collection.
     *
     * @return int
     */
    public function size()
    {
        return iterator_count($this);
    }

    /**
     * Returns value at the key $key. If multiple values have this key, return first. If no value has this key, return $ifNotFound.
     *
     * @param mixed $key
     * @param mixed|null $ifNotFound
     * @return mixed
     */
    public function get($key, $ifNotFound = null)
    {
        return $this->find(
            function ($k, $v) use ($key) {
                return $k == $key;
            },
            $ifNotFound
        );
    }

    /**
     * Returns first value matched by $filter. If no value matches, return $ifNotFound.
     *
     * @param callable $filter
     * @param mixed|null $ifNotFound
     * @return mixed
     */
    public function find(callable $filter, $ifNotFound = null)
    {
        $filtered = new FilteredCollection($this, $filter);
        foreach ($filtered as $value) {
            return $value;
        }

        return $ifNotFound;
    }

    /**
     * Returns a collection of items whose keys are the return values of $differentiator and values are the number of items in this collection for which the $differentiator returned this value.
     *
     * @param callable $differentiator
     * @return Collection
     */
    public function countBy(callable $differentiator)
    {
        return new CountByCollection($this, $differentiator);
    }

    /**
     * Returns a lazy collection by changing keys of this collection for each item to the result of $indexer for that key/value.
     *
     * @param callable $indexer
     * @return Collection
     */
    public function indexBy(callable $indexer)
    {
        $usesKeys = $this->getNumberOfArguments($indexer) == 2;

        return new MappedCollection(
            $this,
            function ($k, $v) use ($indexer, $usesKeys) {
                yield $usesKeys ? $indexer($k, $v) : $indexer($v);
                yield $v;
            }
        );
    }

    /**
     * Returns true if $predicament returns true for every item in this collection, false otherwise.
     *
     * @param callable $predicament
     * @return bool
     */
    public function every(callable $predicament)
    {
        $usesKeys = $this->getNumberOfArguments($predicament) == 2;

        foreach ($this as $k => $v) {
            $passed = $usesKeys ? $predicament($k, $v) : $predicament($v);
            if (!$passed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if $predicament returns true for at least one item in this collection, false otherwise.
     *
     * @param callable $predicament
     * @return bool
     */
    public function some(callable $predicament)
    {
        $usesKeys = $this->getNumberOfArguments($predicament) == 2;

        foreach ($this as $k => $v) {
            $passed = $usesKeys ? $predicament($k, $v) : $predicament($v);
            if ($passed) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if $needle is present in the collection.
     *
     * @param mixed $needle
     * @return bool
     */
    public function contains($needle)
    {
        return $this->some(function ($v) use ($needle) {
            return $v === $needle;
        });
    }

    /**
     * Returns collection of items in this collection in reverse order.
     *
     * @return ReversedCollection
     */
    public function reverse()
    {
        return new ReversedCollection($this);
    }

    /**
     * Reduce the collection to single value. Walks from right to left.
     *
     * @param mixed $startValue
     * @param callable $reduction Must take 2 arguments, intermediate value and item from the iterator.
     * @return mixed
     */
    public function reduceRight($startValue, callable $reduction)
    {
        return $this->reverse()->reduce($startValue, $reduction);
    }

    /**
     * A form of slice that returns first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function take($numberOfItems)
    {
        return $this->slice(0, $numberOfItems);
    }

    /**
     * A form of slice that returns all but first $numberOfItems items.
     *
     * @param int $numberOfItems
     * @return Collection
     */
    public function drop($numberOfItems)
    {
        return $this->slice($numberOfItems + 1);
    }

    /**
     * Returns collection of items from this collection but with keys being numerical from 0 upwards.
     *
     * @return Collection
     */
    public function resetKeys()
    {
        return new ResetKeysCollection($this);
    }

    /**
     * Returns lazy collection which is infinite passing last item of this collection to the $iterator and using its return value(s) as next item (and key).
     *
     * If you wish to pass the key, you must yield 2 values from $iterator, first is key, second is item.
     *
     * @param callable $iterator
     * @return Collection
     */
    public function iterate(callable $iterator)
    {
        return new IteratingCollection($this, $iterator);
    }

    /**
     * Returns first item matched by $filter, converted to Collection if possible (i.e. if it is Traversable or array). If no value matches, return $ifNotFound.
     *
     * @param callable $filter
     * @param mixed|null $ifNotFound
     * @return mixed
     */
    public function findCollection(callable $filter, $ifNotFound = null)
    {
        $found = $this->find($filter, $ifNotFound);

        if ($this->canBeConvertedToCollection($found)) {
            $found = new Collection($found);
        }

        return $found;
    }

    /**
     * Returns item at the key $key converted to Collection if possible (i.e. if it is Traversable or array). If multiple values have this key, return first. If no value has this key, return $ifNotFound.
     *
     * @param mixed $key
     * @param mixed|null $ifNotFound
     * @return mixed
     */
    public function getCollection($key, $ifNotFound = null)
    {
        $found = $this->get($key, $ifNotFound);

        if ($this->canBeConvertedToCollection($found)) {
            $found = new Collection($found);
        }

        return $found;
    }

    /**
     * Returns a lazy collection without elements matched by $filter. If you wish to work with keys, pass a callable that has 2 input values ($key, $value).
     *
     * @param callable $filter
     * @return Collection
     */
    public function reject(callable $filter)
    {
        $usesKeys = $this->getNumberOfArguments($filter) == 2;

        return $this->filter(function ($k, $v) use ($filter, $usesKeys) {
            return !call_user_func_array($filter, $usesKeys ? [$k, $v] : [$v]);
        });
    }

    /**
     * Returns a lazy collection of the keys of this collection.
     *
     * @return Collection
     */
    public function keys()
    {
        return $this->map(function ($k, $v) {
            return $k;
        })->resetKeys();
    }

    /**
     * Returns a lazy collection of items of this collection separated by $separator
     *
     * @param mixed $separator
     * @return DropLastCollection
     */
    public function interpose($separator)
    {
        return $this->map(
            function ($v) use ($separator) {
                return [$v, $separator];
            }
        )
            ->flatten(1)
            ->dropLast();
    }

    /**
     * Returns a lazy collection with last $numberOfItems items skipped. These are still iterated over, just skipped.
     *
     * @param int $numberOfItems
     * @return DropLastCollection
     */
    public function dropLast($numberOfItems = 1)
    {
        return new DropLastCollection($this, $numberOfItems);
    }

    /**
     * Returns a lazy collection of first item from first collection, first item from second, second from first and so on.
     *
     * @param Traversable|array $collection
     * @return InterleavedCollection
     */
    public function interleave($collection)
    {
        return new InterleavedCollection($this, $collection);
    }

    /**
     * Returns an infinite lazy collection of items in this collection repeated infinitely.
     *
     * @return CycledCollection
     */
    public function cycle()
    {
        return new CycledCollection($this);
    }

    /**
     * Returns a lazy collection of items of this collection with $item added as first element. Its key will be 0.
     *
     * @param mixed $item
     * @return Collection
     */
    public function prepend($item)
    {
        return $this->prependWithKey(0, $item);
    }

    /**
     * Returns a lazy collection of items of this collection with $item added as first element. Its key will be $key.
     *
     * @param mixed $key
     * @param mixed $item
     * @return Collection
     */
    public function prependWithKey($key, $item)
    {
        return (new Collection([$key => $item]))->concat($this);
    }

    /**
     * Returns a lazy collection of items of this collection with $item added as last element. Its key will be 0.
     *
     * @param mixed $item
     * @return Collection
     */
    public function append($item)
    {
        return $this->appendWithKey(0, $item);
    }

    /**
     * Returns a lazy collection of items of this collection with $item added as last element. Its key will be $key.
     *
     * @param mixed $key
     * @param mixed $item
     * @return Collection
     */
    public function appendWithKey($key, $item)
    {
        return $this->concat([$key => $item]);
    }

    /**
     * Returns a lazy collection by removing items from this collection until first item for which $predicament returns false.
     *
     * @param callable $predicament
     * @return Collection
     */
    public function dropWhile(callable $predicament)
    {
        $usesKeys = $this->getNumberOfArguments($predicament) == 2;
        $failedAlready = false;

        return $this->reject(function ($k, $v) use ($usesKeys, &$failedAlready, $predicament) {
            if (!$failedAlready) {
                $failedAlready = call_user_func_array($predicament, $usesKeys ? [$k, $v] : [$v]);

                return $failedAlready;
            }

            return false;
        });
    }

    /**
     * Returns a lazy collection which is a result of calling map($mapper) and then flatten(1)
     *
     * @param callable $mapper
     * @return Collection
     */
    public function mapcat(callable $mapper)
    {
        return $this->map($mapper)->flatten(1);
    }

    /**
     * Returns a lazy collection of items from the start of the collection until the first item for which $predicament returns false.
     *
     * @param callable $predicament
     * @return Collection
     */
    public function takeWhile(callable $predicament)
    {
        $usesKeys = $this->getNumberOfArguments($predicament) == 2;
        $failedAlready = false;

        return $this->filter(function ($k, $v) use ($usesKeys, &$failedAlready, $predicament) {
            if (!$failedAlready) {
                $failedAlready = $usesKeys ? $predicament($k, $v) : $predicament($v);

                return $failedAlready;
            }

            return false;
        });
    }

    /**
     * Returns a collection of [take($position), drop($position]
     *
     * @param int $position
     * @return Collection
     */
    public function splitAt($position)
    {
        return new Collection([$this->take($position), $this->drop($position)]);
    }

    /**
     * Returns a collection of [takeWhile($predicament), dropWhile($predicament]
     *
     * @param callable $predicament
     * @return Collection
     */
    public function splitWith(callable $predicament)
    {
        return new Collection([$this->takeWhile($predicament), $this->dropWhile($predicament)]);
    }

    /**
     * Returns a lazy collection with items from this collection equal to any key in $replacementMap replaced for their value.
     *
     * @param Traversable|array $replacementMap
     * @return Collection
     */
    public function replace($replacementMap)
    {
        $replacementMap = new Collection($replacementMap);
        $ifNotNullIdentifier = new stdClass();

        return $this->map(function ($v) use ($replacementMap, $ifNotNullIdentifier) {
            $result = $replacementMap->get($v, $ifNotNullIdentifier);
            if ($result !== $ifNotNullIdentifier) {
                $v = $result;
            }

            return $v;
        });
    }

    /**
     * Returns a lazy collection of reduction steps.
     *
     * @param mixed $startValue
     * @param callable $reduction
     * @return Collection
     */
    public function reductions($startValue, callable $reduction)
    {
        return $this->map(function ($item) use (&$startValue, $reduction) {
            $startValue = $reduction($startValue, $item);

            return $startValue;
        });
    }

    /**
     * Returns a lazy collection of every nth item in this collection
     *
     * @param int $step
     * @return Collection
     */
    public function takeNth($step)
    {
        $counter = 0;

        return $this->filter(function () use (&$counter, $step) {
            return $counter++ % $step == 0;
        });
    }

    /**
     * Returns a collection of shuffled items from this collection
     *
     * @return Collection
     */
    public function shuffle()
    {
        $shuffledArray = $this
            ->map(function ($k, $v) {
                return [$k, $v];
            })
            ->resetKeys()
            ->toArray();

        shuffle($shuffledArray);

        return (new Collection($shuffledArray))
            ->map(function ($v) {
                yield $v[0];
                yield $v[1];
            });
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
     * @return PartitionedCollection
     */
    public function partition($numberOfItems, $step = 0, $padding = [])
    {
        return new PartitionedCollection($this, $numberOfItems, $step, $padding);
    }

    /**
     * Creates a lazy collection of collections created by partitioning this collection every time $partitioning will return different result.
     *
     * @param callable $partitioning
     * @return PartitionedByCollection
     */
    public function partitionBy(callable $partitioning)
    {
        return new PartitionedByCollection($this, $partitioning);
    }

    /**
     * Returns true if this collection is empty. False otherwise.
     *
     * @return bool
     */
    public function isEmpty()
    {
        $this->rewind();

        return !$this->valid();
    }

    /**
     * Opposite of isEmpty.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     * Returns a collection where keys are distinct items from this collection and their values are number of occurrences of each value.
     *
     * @return Collection
     */
    public function frequencies()
    {
        return $this->countBy(function ($v) {
            return $v;
        });
    }

    /**
     * @param mixed $item
     * @return bool
     */
    protected function canBeConvertedToCollection($item)
    {
        return is_array($item) || ($item instanceof Traversable && !($item instanceof Collection));
    }

    /**
     * @param callable $reduction
     * @return int
     */
    protected function getNumberOfArguments(callable $reduction)
    {
        if (is_array($reduction) && count($reduction) == 2) {
            return (new \ReflectionMethod($reduction[0], $reduction[1]))->getNumberOfParameters();
        } else {
            return (new ReflectionFunction($reduction))->getNumberOfParameters();
        }
    }
}
