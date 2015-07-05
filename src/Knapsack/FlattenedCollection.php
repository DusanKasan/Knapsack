<?php

namespace Knapsack;

class FlattenedCollection extends Collection
{
    /**
     * @param array|\Traversable $input
     * @param int $depth
     */
    public function __construct($input, $depth = -1)
    {
        parent::__construct($input);
        $recursiveIterator = new CompleteRecursiveIteratorIterator(new UniversalRecursiveIterator($this->input));
        $recursiveIterator->setMaxDepth($depth);

        $this->input = $recursiveIterator;
    }
}
