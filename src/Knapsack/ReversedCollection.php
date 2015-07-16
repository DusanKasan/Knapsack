<?php

namespace Knapsack;

use Traversable;

class ReversedCollection extends Collection
{
    /**
     * @var array|Traversable
     */
    private $originalInput;

    /**
     * @param array|Traversable $input
     */
    public function __construct($input)
    {
        $this->originalInput = $input;
    }

    public function rewind()
    {
        $tmp = [];
        foreach ($this->originalInput as $k => $v) {
            $tmp[] = [$k, $v];
        }

        $collection = new Collection(array_reverse($tmp));
        $this->input = $collection->map(function ($v) {
            yield $v[0];
            yield $v[1];
        });

        parent::rewind();
    }
}
