<?php

namespace Knapsack;

use Knapsack\Callback\Callback;
use Knapsack\Exceptions\NoMoreItems;
use Traversable;

class IteratingCollection extends MappedCollection
{
    /**
     * @var Callback
     */
    private $followedCallback;

    /**
     * @param array|Traversable $input
     * @param callable $followedCallback
     */
    public function __construct($input, callable $followedCallback)
    {
        parent::__construct($input, $followedCallback);
        $this->followedCallback = new Callback($followedCallback);
    }

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
