<?php

namespace Knapsack;

use Traversable;

class FilteredCollection extends Collection
{
    /**
     * @var callable
     */
    private $filterCallback;

    /**
     * @var bool
     */
    private $filterUsingKeys;

    /**
     * @param array|Traversable $input
     * @param callable $filter
     */
    public function __construct($input, callable $filter)
    {
        parent::__construct($input);
        $this->filterCallback = $filter;
        $this->filterUsingKeys = (new \ReflectionFunction($filter))->getNumberOfParameters() == 2;
    }

    public function valid()
    {
        while (!$this->executeFilter($this->key(), $this->current()) && parent::valid()) {
            $this->next();
        }

        return parent::valid();
    }

    /**
     * @param mixed $key
     * @param mixed $item
     * @return mixed
     */
    private function executeFilter($key, $item)
    {
        $filter = $this->filterCallback;
        if ($this->filterUsingKeys) {
            return $filter($key, $item);
        } else {
            return $filter($item);
        }
    }
}
