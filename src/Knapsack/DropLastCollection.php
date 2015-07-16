<?php

namespace Knapsack;

use Traversable;

class DropLastCollection extends Collection
{
    /**
     * @var int
     */
    private $numberOfItems;

    /**
     * @var array
     */
    private $buffer;

    /**
     * @param array|Traversable $input
     * @param int $numberOfItems
     */
    public function __construct($input, $numberOfItems)
    {
        parent::__construct($input);
        $this->numberOfItems = $numberOfItems;
    }

    public function rewind()
    {
        $this->input->rewind();

        $this->buffer = [];
        foreach ($this->input as $key => $item) {
            $this->buffer[] = [$key, $item];
            if (count($this->buffer) == $this->numberOfItems) {
                $this->input->next();
                break;
            }
        }
    }

    public function next()
    {
        array_shift($this->buffer);
        $this->buffer[] = [$this->input->key(), $this->input->current()];
        $this->input->next();
    }

    public function current()
    {
        return reset($this->buffer)[1];
    }

    public function key()
    {
        return reset($this->buffer)[0];
    }
}
