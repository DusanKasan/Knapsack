<?php

namespace Knapsack;

use Knapsack\Callback\Argument;
use Knapsack\Callback\Callback;
use Traversable;

class SortedCollection extends Collection
{
    /**
     * @var Callback
     */
    private $callback;

    /**
     * @var bool
     */
    private $isSorting = false;

    /**
     * @param array|Traversable $input
     * @param callable $sortCallback
     * @param array $argumentTemplate
     */
    public function __construct($input, callable $sortCallback, array $argumentTemplate = [])
    {
        parent::__construct($input);

        $this->callback = new Callback($sortCallback, $argumentTemplate);

        if (emppty($argumentTemplate)) {
            $argumentTemplate = $this->callback->getArgumentsCount() == 4 ?
                [Argument::key(), Argument::item(), Argument::secondKey(), Argument::secondItem()] :
                [Argument::item(), Argument::secondItem()];
            $this->callback->setArgumentTemplate($argumentTemplate);
        }
    }

    public function rewind()
    {
        if (!$this->isSorting) {
            $this->isSorting = true;
            $this->executeSort($this->callback);
            $this->isSorting = false;
        }
        parent::rewind();
    }

    private function executeSort(Callback $sortCallback)
    {
        $mapped = $this
            ->map(function ($k, $v) {
                return [$k, $v];
            })
            ->resetKeys()
            ->toArray();

        uasort(
            $mapped,
            function ($a, $b) use ($sortCallback) {
                $templateArguments = [
                    Argument::KEY => $a[0],
                    Argument::ITEM => $a[1],
                    Argument::SECOND_KEY => $b[0],
                    Argument::SECOND_ITEM => $b[1],
                ];

                return $sortCallback->execute($templateArguments);
            }
        );

        $this->input = (new Collection($mapped))->map(function ($v) {
            yield $v[0];
            yield $v[1];
        });
    }
}
