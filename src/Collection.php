<?php

namespace DusanKasan\Knapsack;

use ArrayIterator;
use DusanKasan\Knapsack\Exceptions\InvalidArgument;
use DusanKasan\Knapsack\Exceptions\InvalidReturnValue;
use IteratorAggregate;
use Traversable;

/**
 * @template TKey
 * @template TVal
 * @implements CollectionInterface<TKey, TVal>
 * @implements IteratorAggregate<TKey, TVal>
 */
class Collection implements IteratorAggregate, \Serializable, CollectionInterface
{
    /**
     * @use CollectionTrait<TKey, TVal>
     */
    use CollectionTrait;

    /**
     * @var Traversable<TKey, TVal>
     */
    protected $input;

    /**
     * @var callable():iterable<TKey, TVal>|null
     */
    private $inputFactory = null;

    /**
     * @param callable(): iterable<TKey, TVal>|iterable<TKey, TVal> $input
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
     * @template CKey
     * @template CVal
     * @param callable():iterable<CKey, CVal>|iterable<CKey, CVal> $input
     * @return static<CKey, CVal>
     */
    public static function from($input)
    {
        return new static($input);
    }

    /**
     * Returns lazy collection of values, where first value is $input and all subsequent values are computed by applying
     * $function to the last value in the collection. By default this produces an infinite collection. However you can
     * end the collection by throwing a NoMoreItems exception.
     *
     * @param mixed $input
     * @param callable(mixed):mixed $function
     * @return static
     */
    public static function iterate($input, callable $function)
    {
        return static::from(iterate($input, $function));
    }

    /**
     * Returns a lazy collection of $value repeated $times times. If $times is not provided the collection is infinite.
     *
     * @template TItem
     * @param TItem $value
     * @param int $times
     * @return static<int, TItem>
     */
    public static function repeat($value, int $times = -1)
    {
        return static::from(repeat($value, $times));
    }

    /**
     * Returns a lazy collection of numbers starting at $start, incremented by $step until $end is reached.
     *
     * @param int $start
     * @param int|null $end
     * @param int $step
     * @return static<int, int>
     */
    public static function range($start = 0, $end = null, $step = 1)
    {
        return static::from(\DusanKasan\Knapsack\range($start, $end, $step));
    }

    /**
     * {@inheritdoc}
     * @throws InvalidReturnValue
     * @return Traversable<TKey, TVal>
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
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->input = dereferenceKeyValue(unserialize($serialized));
    }


    /**
     * @return iterable<TKey, TVal>
     */
    protected function getItems(): iterable
    {
        return $this;
    }
}
