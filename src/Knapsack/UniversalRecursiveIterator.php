<?php

namespace Knapsack;

use ArrayIterator;
use IteratorIterator;
use RecursiveIterator;
use Traversable;

class UniversalRecursiveIterator extends IteratorIterator implements RecursiveIterator
{

    /**
     * @inheritdoc
     */
    public function hasChildren()
    {
        $current = $this->current();

        return is_array($current) || (is_object($current) && $current instanceof Traversable);
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        $result = NULL;
        $current = $this->current();

        if (is_array($current)) {
            $result = new self(new ArrayIterator($current));
        } elseif (is_object($current) && $current instanceof Traversable) {
            $result = new self($current);
        }

        return $result;
    }
}
