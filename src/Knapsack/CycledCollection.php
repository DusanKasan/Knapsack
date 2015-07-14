<?php

namespace Knapsack;

class CycledCollection extends Collection
{
    public function valid()
    {
        $valid = parent::valid();

        if (!$valid) {
            $this->rewind();
        }

        return true;
    }
}
