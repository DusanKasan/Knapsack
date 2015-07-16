<?php

namespace Knapsack;

use Generator;
use ReflectionFunction;
use Traversable;

class MappedCollection extends Collection
{
    /**
     * @var callable
     */
    private $mapping;

    /**
     * @var bool
     */
    private $mapUsingKeys;

    /**
     * @var mixed
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param array|Traversable $input
     * @param callable $callback
     */
    public function __construct($input, callable $callback)
    {
        parent::__construct($input);
        $this->mapping = $callback;
        $this->mapUsingKeys = $this->getNumberOfArguments($callback) == 2;
    }

    public function valid()
    {
        $valid = parent::valid();

        if ($valid) {
            $this->executeMapping($this->input->key(), $this->input->current());
        }

        return $valid;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    private function executeMapping($key, $value)
    {
        $mapping = $this->mapping;
        if ($this->mapUsingKeys) {
            $mapped = $mapping($key, $value);
        } else {
            $mapped = $mapping($value);
        }

        if ($mapped instanceof Generator) {
            $this->resolveGeneratorMapping($key, $mapped);
        } else {
            $this->key = $key;
            $this->value = $mapped;
        }
    }

    /**
     * @param mixed $key
     * @param Generator $mapped
     */
    private function resolveGeneratorMapping($key, Generator $mapped)
    {
        $arr = iterator_to_array($mapped);

        if (count($arr) == 1) {
            $this->key = $key;
            $this->value = $arr[0];
        } else {
            $this->key = $arr[0];
            $this->value = $arr[1];
        }
    }

    public function current()
    {
        return $this->value;
    }

    public function key()
    {
        return $this->key;
    }
}
