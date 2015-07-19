<?php

namespace Knapsack;

use Knapsack\Callback\Callback;
use Traversable;

class FilteredCollection extends Collection
{
    /**
     * @var Callback
     */
    private $filterCallback;

    /**
     * @param array|Traversable $input
     * @param callable $filter
     */
    public function __construct($input, callable $filter)
    {
        parent::__construct($input);
        $this->filterCallback = new Callback($filter);
    }

    public function valid()
    {
        while (
            parent::valid() &&
            !$this->filterCallback->executeWithKeyAndValue($this->key(), $this->current())
        ) {
            $this->next();
        }

        return parent::valid();
    }
}
