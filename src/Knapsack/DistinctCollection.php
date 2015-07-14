<?php

namespace Knapsack;

class DistinctCollection extends Collection
{
    /**
     * @var array
     */
    private $distinctValues = [];

    public function valid()
    {
        while (parent::valid() && in_array($this->current(), $this->distinctValues)) {
            $this->next();
        }

        if (parent::valid()) {
            $this->distinctValues[] = $this->current();
        }

        return parent::valid();
    }

    public function rewind()
    {
        $this->distinctValues = [];
        parent::rewind();
    }
}
