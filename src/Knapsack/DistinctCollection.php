<?php

namespace Knapsack;

class DistinctCollection extends Collection
{
    /**
     * @var array
     */
    private $distinctValues = [];

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->distinctValues = [];
        parent::rewind();
    }
}
