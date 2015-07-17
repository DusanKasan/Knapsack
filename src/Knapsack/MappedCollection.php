<?php

namespace Knapsack;

use Generator;
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
    private $item;

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
     * @param mixed $item
     */
    private function executeMapping($key, $item)
    {
        $mapping = $this->mapping;
        if ($this->mapUsingKeys) {
            $mapped = $mapping($key, $item);
        } else {
            $mapped = $mapping($item);
        }

        if ($mapped instanceof Generator) {
            $this->resolveGeneratorMapping($key, $mapped);
        } else {
            $this->key = $key;
            $this->item = $mapped;
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
            $this->item = $arr[0];
        } else {
            $this->key = $arr[0];
            $this->item = $arr[1];
        }
    }

    public function current()
    {
        return $this->item;
    }

    public function key()
    {
        return $this->key;
    }
}
