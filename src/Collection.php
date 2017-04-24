<?php

namespace DusanKasan\Knapsack;

use ArrayIterator;
use DusanKasan\Knapsack\Exceptions\InvalidArgument;
use DusanKasan\Knapsack\Exceptions\InvalidReturnValue;
use IteratorAggregate;
use Traversable;

class Collection implements IteratorAggregate, \Serializable
{
    use CollectionTrait;

    /**
     * @var Traversable
     */
    protected $input;

    /**
     * @var callable
     */
    private $inputFactory;

    /**
     * @param callable|array|Traversable $input If callable is passed, it must return an array|Traversable.
     */
    public function __construct($input)
    {
        if (is_callable($input)) {
            $this->inputFactory = $input;
            $input = $input();
        }

        if (is_array($input)) {
            $this->input = new ArrayIterator($input);
        } elseif ($input instanceof Traversable) {
            $this->input = $input;
        } else {
            throw $this->inputFactory ? new InvalidReturnValue : new InvalidArgument;
        }
    }

    /**
     * Static alias of normal constructor.
     *
     * @param callable|array|Traversable $input
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
     * {@inheritdoc}
     * @throws InvalidReturnValue
     */
    public function getIterator()
    {
        if ($this->inputFactory) {
            $input = call_user_func($this->inputFactory);

            if (is_array($input)) {
                $input = new ArrayIterator($input);
            }

            if (!($input instanceof Traversable)) {
                throw new InvalidReturnValue;
            }

            $this->input = $input;
        }

        return $this->input;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            toArray(
                map(
                    $this->input,
                    function ($value, $key) {
                        return [$key, $value];
                    }
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->input = dereferenceKeyValue(unserialize($serialized));
    }
}
