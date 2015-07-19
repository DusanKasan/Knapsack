<?php

namespace Knapsack;

use Iterator;
use Knapsack\Callback\Callback;
use Traversable;

class GroupedCollection extends Collection
{
    /**
     * @var Callback
     */
    private $grouping;

    /**
     * @var Iterator
     */
    private $originalInput;

    /**
     * @param array|Traversable $input
     * @param callable $grouping
     */
    public function __construct($input, callable $grouping)
    {
        parent::__construct($input);
        $this->originalInput = $this->input;
        $this->grouping = new Callback($grouping);
    }

    public function rewind()
    {
        $this->group();
        parent::rewind();
    }

    private function group()
    {
        $input = [];

        foreach ($this->originalInput as $key => $item) {
            $key = $this->grouping->executeWithKeyAndValue($key, $item);
            $input[$key][] = $item;
        }

        $this->input = new Collection($input);
    }
}
