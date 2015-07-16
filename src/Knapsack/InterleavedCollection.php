<?php

namespace Knapsack;

use Traversable;

class InterleavedCollection extends Collection
{
    /**
     * @var Collection[]
     */
    private $collections = [];

    private $buffer = [];

    /**
     * @param array|Traversable $firstCollection
     * @param array|Traversable $secondCollection
     */
    public function __construct($firstCollection, $secondCollection)
    {
        $this->collections[] = new Collection($firstCollection);
        $this->collections[] = new Collection($secondCollection);
    }

    public function rewind()
    {
        $this->buffer = [];
        foreach ($this->collections as $collection) {
            $collection->rewind();
        }

        $this->bufferFirstItems();
    }

    private function bufferFirstItems()
    {
        foreach ($this->collections as $collection) {
            if ($collection->valid()) {
                $key = $collection->key();
                $item = $collection->current();
                $this->buffer[] = [$key, $item];
            }
        }
    }

    public function current()
    {
        return reset($this->buffer)[1];
    }

    public function key()
    {
        return reset($this->buffer)[0];
    }

    public function next()
    {
        if (count($this->buffer) != 0) {
            array_shift($this->buffer);
        }

        if (count($this->buffer) == 0) {
            foreach ($this->collections as $collection) {
                if ($collection->valid()) {
                    $collection->next();
                }
            }

            $this->bufferFirstItems();
        }
    }

    public function valid()
    {
        return !empty($this->buffer);
    }


}
