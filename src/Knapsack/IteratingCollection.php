<?php

namespace Knapsack;

use Generator;
use Knapsack\Exceptions\NoMoreItems;
use ReflectionFunction;

class IteratingCollection extends Collection
{
    /**
     * @var callable
     */
    private $followedCallback;

    private $key;
    private $item;
    private $callbackUsesKeys;

    /**
     * @param array|\Traversable $input
     * @param callable $followedCallback
     */
    public function __construct($input, callable $followedCallback)
    {
        parent::__construct($input);
        $this->followedCallback = $followedCallback;
        $this->callbackUsesKeys = (new ReflectionFunction($followedCallback))->getNumberOfParameters() == 2;
    }

    public function valid()
    {
        if (parent::valid()) {
            $this->item = $this->input->current();
            $this->key = $this->input->key();
        } else {
            try {
                $this->executeMapping($this->key, $this->item);
            } catch (NoMoreItems $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    private function executeMapping($key, $value)
    {
        $mapping = $this->followedCallback;
        if ($this->callbackUsesKeys) {
            $mapped = $mapping($key, $value);
        } else {
            $mapped = $mapping($value);
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

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->item;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->key;
    }
}
