<?php

namespace DusanKasan\Knapsack;

use Closure;
use DusanKasan\Knapsack\Exceptions\InvalidArgument;
use Iterator;
use IteratorAggregate;
use RecursiveArrayIterator;
use Traversable;

class Collection implements Iterator
{
    use CollectionTrait;

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
        if (is_callable($input)) {
            $this->generatorFactory = $input;
            $this->input = $input();
        } elseif (is_array($input)) {
            $input = new RecursiveArrayIterator($input);
            $this->input = $input;
        } elseif ($input instanceof IteratorAggregate) {
            $input = $input->getIterator();
            $this->input = $input;
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
}
