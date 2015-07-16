<?php

namespace Knapsack;

use Traversable;

class ResetKeysCollection extends Collection
{
    private $key = 0;

    /**
     * @param Traversable|array $input
     */
    public function __construct($input)
    {
        parent::__construct($input);
    }

    public function rewind()
    {
        $this->key = 0;
        parent::rewind();
    }

    public function next()
    {
        $this->key++;
        parent::next();
    }

    public function key()
    {
        return $this->key;
    }
}
