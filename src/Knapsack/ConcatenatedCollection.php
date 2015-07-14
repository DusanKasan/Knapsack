<?php

namespace Knapsack;

class ConcatenatedCollection extends Collection
{
    /**
     * @var Collection
     */
    private $input1;

    /**
     * @var Collection
     */
    private $input2;

    /**
     * @var bool
     */
    private $usingSecond = false;

    /**
     * This is not done using AppendIterator because if you are changing iterators it is using on the fly, it can get stuck in infinite loop. On the other hand, it might be just some stupid bug in my code.
     *
     * @param array|\Traversable $input1
     * @param array|\Traversable $input2
     */
    public function __construct($input1, $input2)
    {
        $this->input1 = new Collection($input1);
        $this->input2 = new Collection($input2);

        parent::__construct($this->input1);
    }

    public function valid()
    {
        $valid = parent::valid();

        if (!$this->usingSecond && !$valid) {
            $this->input = $this->input2;
            $valid = $this->input2->valid();
        }

        return $valid;
    }

    public function rewind()
    {
        parent::rewind();

        $this->input1->rewind();
        $this->input2->rewind();
        $this->usingSecond = false;
        $this->input = $this->input1;
    }
}
