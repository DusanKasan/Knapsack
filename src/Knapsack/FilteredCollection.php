<?php

namespace Knapsack;

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
     * @param array|\Traversable $input
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
     * @param mixed $value
     * @return mixed
     */
    private function executeFilter($key, $value)
    {
        $filter = $this->filterCallback;
        if ($this->filterUsingKeys) {
            return $filter($key, $value);
        } else {
            return $filter($value);
        }
    }
}
