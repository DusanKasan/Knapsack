<?php

namespace Knapsack;

use Knapsack\Callback\Callback;
use Traversable;

class ForEachCollection extends Collection
{
    /**
     * @var Callback
     */
    private $callback;

    /**
     * @param array|Traversable $input
     * @param callable $callback
     * @param array $argumentTemplate
     */
    public function __construct($input, callable $callback, array $argumentTemplate = [])
    {
        parent::__construct($input);
        $this->callback = new Callback($callback, $argumentTemplate);
    }

    public function current()
    {
        $current = parent::current();
        $this->callback->executeWithKeyAndValue($this->key(), $current);

        return $current;
    }
}
