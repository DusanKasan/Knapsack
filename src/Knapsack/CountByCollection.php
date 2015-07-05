<?php

namespace Knapsack;

class CountByCollection extends GroupedCollection
{
    public function current()
    {
        return (new Collection(parent::current()))->size();
    }
}
