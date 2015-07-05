<?php

namespace Knapsack;


use RecursiveIteratorIterator;

class CompleteRecursiveIteratorIterator extends RecursiveIteratorIterator
{
    /**
     * @inheritdoc
     */
    public function callHasChildren()
    {
        $hasChildren = parent::callHasChildren();

        if ($hasChildren && $this->getMaxDepth() !== FALSE && $this->getDepth() == $this->getMaxDepth()) {
            return FALSE;
        }

        return $hasChildren;
    }
}
