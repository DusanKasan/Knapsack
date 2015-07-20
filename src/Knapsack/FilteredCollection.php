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
     * @param array $argumentTemplate
     */
    public function __construct($input, callable $filter, array $argumentTemplate = [])
    {
        parent::__construct($input);
        $this->filterCallback = new Callback($filter, $argumentTemplate);
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
