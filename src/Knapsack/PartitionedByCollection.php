<?php

namespace Knapsack;

use Knapsack\Callback\Callback;
use Traversable;

class PartitionedByCollection extends Collection
{
    /**
     * @var Callback
     */
    private $partitioning;

    /**
     * @var int
     */
    private $key;


    /**
     * @param array|Traversable $input
     * @param callable $partitioning
     */
    public function __construct($input, callable $partitioning)
    {
        parent::__construct($input);
        $this->partitioning = new Callback($partitioning);
    }

    public function rewind()
    {
        $this->key = 0;
        parent::rewind();
    }

    public function current()
    {
        $buffer = [];

        if ($this->input->valid()) {
            $key = $this->input->key();
            $item = $this->input->current();
            $lastResult = $this->partitioning->executeWithKeyAndValue($key, $item);
            $this->input->next();
            $buffer[] = [$key, $item];

            while ($this->input->valid()) {
                $key = $this->input->key();
                $item = $this->input->current();
                if ($lastResult != $this->partitioning->executeWithKeyAndValue($key, $item)) {
                    break;
                }

                $buffer[] = [$key, $item];
                $this->input->next();
            }
        }


        return (new Collection($buffer))->map(function ($v) {
            yield $v[0];
            yield $v[1];
        });
    }

    public function key()
    {
        return $this->key;
    }

    public function next()
    {
        $this->key++;
    }
}
