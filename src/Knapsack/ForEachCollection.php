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
     */
    public function __construct($input, callable $callback)
    {
        parent::__construct($input);
        $this->callback = new Callback($callback);
    }

    public function current()
    {
        $current = parent::current();
        $this->callback->executeWithKeyAndValue($this->key(), $current);

        return $current;
    }
}
