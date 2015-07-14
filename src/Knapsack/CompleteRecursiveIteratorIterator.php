<?php

namespace Knapsack;


use RecursiveIteratorIterator;

class CompleteRecursiveIteratorIterator extends RecursiveIteratorIterator
{
    public function callHasChildren()
    {
        $hasChildren = parent::callHasChildren();

        if ($hasChildren && $this->getMaxDepth() !== false && $this->getDepth() == $this->getMaxDepth()) {
            return false;
        }

        return $hasChildren;
    }
}
