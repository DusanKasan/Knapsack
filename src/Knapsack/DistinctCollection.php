<?php

namespace Knapsack;

class DistinctCollection extends Collection
{
    /**
     * @var array
     */
    private $distinctItems = [];

    public function valid()
    {
        while (parent::valid() && in_array($this->current(), $this->distinctItems)) {
            $this->next();
        }

        if (parent::valid()) {
            $this->distinctItems[] = $this->current();
        }

        return parent::valid();
    }

    public function rewind()
    {
        $this->distinctItems = [];
        parent::rewind();
    }
}
