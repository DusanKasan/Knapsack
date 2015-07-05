<?php

namespace Knapsack;

class PartitionedCollection extends Collection
{

    /**
     * @var array
     */
    private $buffer = [];

    /**
     * @var
     */
    private $numberOfItems;

    /**
     * @var int|false
     */
    private $step;

    /**
     * @var array
     */
    private $padding;

    /**
     * @var bool
     */
    private $backTrack;

    /**
     * @var int
     */
    private $key;

    public function __construct($input, $numberOfItems, $step = 0, $padding = [])
    {
        if ($this->canBeConvertedToCollection($input)) {
            $input = new Collection($input);
        }

        $this->input = $input;
        $this->numberOfItems = $numberOfItems;
        $this->step = $step ?: $numberOfItems;
        $this->padding = $padding;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return parent::valid() || $this->backTrack;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        parent::rewind();
        $this->key = 0;
        $this->backTrack = FALSE;
        $count = 0;
        while ($this->input->valid() && $count++ < $this->numberOfItems) {
            $this->buffer[] = [$this->input->key(), $this->input->current()];
            $this->input->next();
        }
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        $current = new Collection($this->buffer);

        return $current->map(function ($v) {
            yield $v[0];
            yield $v[1];
        })->take($this->numberOfItems);
    }

    public function key()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->key++;

        for ($i = 0; $i < $this->step; $i++) {
            array_shift($this->buffer);
        }

        $this->backTrack = FALSE;
        $count = 0;
        while ($this->input->valid() && $count++ < $this->numberOfItems) {
            $this->buffer[] = [$this->input->key(), $this->input->current()];
            $this->input->next();
            $this->backTrack = TRUE;
        }

        foreach ($this->padding as $k => $v) {
            if ($count++ >= $this->numberOfItems) {
                break;
            }

            $this->buffer[] = [$k, $v];
        }
    }
}
