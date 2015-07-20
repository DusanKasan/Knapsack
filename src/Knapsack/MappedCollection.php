<?php

namespace Knapsack;

use Generator;
use Knapsack\Callback\Callback;
use Traversable;

class MappedCollection extends Collection
{
    /**
     * @var Callback
     */
    protected $mapping;

    /**
     * @var mixed
     */
    protected $key;

    /**
     * @var mixed
     */
    protected $item;

    /**
     * @param array|Traversable $input
     * @param callable $callback
     * @param array $argumentTemplate
     */
    public function __construct($input, callable $callback, array $argumentTemplate = [])
    {
        parent::__construct($input);
        $this->mapping = new Callback($callback, $argumentTemplate);
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
    protected function executeMapping($key, $item)
    {
        $mapped = $this->mapping->executeWithKeyAndValue($key, $item);

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
    protected function resolveGeneratorMapping($key, Generator $mapped)
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
