<?php

namespace Knapsack;

use Knapsack\Exceptions\NoMoreItems;

class IteratingCollection extends MappedCollection
{
    public function valid()
    {
        if (parent::valid()) {
            $this->item = $this->input->current();
            $this->key = $this->input->key();
        } else {
            try {
                $this->executeMapping($this->key, $this->item);
            } catch (NoMoreItems $e) {
                return false;
            }
        }

        return true;
    }
}
