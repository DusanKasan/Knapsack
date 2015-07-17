<?php

namespace Knapsack;

use Traversable;

class PartitionedByCollection extends Collection
{
    /**
     * @var callable
     */
    private $partitioning;

    /**
     * @var int
     */
    private $key;

    /**
     * @var bool
     */
    private $partitionUsingKeys;

    /**
     * @param array|Traversable $input
     * @param callable $partitioning
     */
    public function __construct($input, callable $partitioning)
    {
        parent::__construct($input);
        $this->partitioning = $partitioning;
        $this->partitionUsingKeys = $this->getNumberOfArguments($partitioning) == 2;
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
            $lastResult = $this->executePartitioning($key, $item);
            $this->input->next();
            $buffer[] = [$key, $item];

            while ($this->input->valid()) {
                $key = $this->input->key();
                $item = $this->input->current();
                if ($lastResult != $this->executePartitioning($key, $item)) {
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

    private function executePartitioning($key, $item)
    {
        $partitioning = $this->partitioning;

        if ($this->partitionUsingKeys) {
            return $partitioning($key, $item);
        } else {
            return $partitioning($item);
        }
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
