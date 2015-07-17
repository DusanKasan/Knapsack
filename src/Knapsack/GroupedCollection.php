<?php

namespace Knapsack;

use Iterator;
use Traversable;

class GroupedCollection extends Collection
{
    /**
     * @var callable
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
        $this->grouping = $grouping;
    }

    public function rewind()
    {
        $this->group();
        parent::rewind();
    }

    private function group()
    {
        $grouping = $this->grouping;
        $input = [];

        foreach ($this->originalInput as $item) {
            $key = $grouping($item);
            $input[$key][] = $item;
        }

        $this->input = new Collection($input);
    }
}
