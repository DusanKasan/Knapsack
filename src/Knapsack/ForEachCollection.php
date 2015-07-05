<?php

namespace Knapsack;

use ReflectionFunction;

class ForEachCollection extends Collection
{
    private $usesKeys;
    private $callback;

    /**
     * @param array|\Traversable $input
     * @param callable $callback
     */
    public function __construct($input, callable $callback)
    {
        parent::__construct($input);
        $this->callback = $callback;
        $this->usesKeys = (new ReflectionFunction($callback))->getNumberOfParameters() == 2;
    }

    public function current()
    {
        $this->executeCallback($this->key(), parent::current());

        return parent::current();
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    private function executeCallback($key, $value)
    {
        $callback = $this->callback;
        if ($this->usesKeys) {
            return $callback($key, $value);
        } else {
            return $callback($value);
        }
    }
}
