<?php

namespace Knapsack;

use Traversable;

class GroupedCollection extends Collection
{
    /**
     * @var callable
     */
    private $grouping;

    /**
     * @param array|Traversable $input
     * @param callable $grouping
     */
    public function __construct($input, callable $grouping)
    {
        parent::__construct($input);
        $this->grouping = $grouping;
    }

    public function rewind()
    {
        parent::rewind();
        $this->group();
    }

    private function group()
    {
        $grouping = $this->grouping;
        $input = [];

        foreach ($this->input as $item) {
            $key = $grouping($item);
            $input[$key][] = $item;
        }

        $this->input = new Collection($input);
    }
}
