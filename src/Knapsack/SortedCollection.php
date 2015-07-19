<?php

namespace Knapsack;

use Knapsack\Callback\Argument;
use Knapsack\Callback\Callback;
use Traversable;

class SortedCollection extends Collection
{
    /**
     * @var callable
     */
    private $sortCallback;

    /**
     * @var bool
     */
    private $isSorting = false;

    /**
     * @param array|Traversable $input
     * @param callable $sortCallback
     */
    public function __construct($input, callable $sortCallback)
    {
        parent::__construct($input);
        $this->sortCallback = $sortCallback;
    }

    public function rewind()
    {
        if (!$this->isSorting) {
            $this->isSorting = true;
            $this->executeSort($this->sortCallback);
            $this->isSorting = false;
        }
        parent::rewind();
    }

    private function executeSort($sortCallback)
    {
        $callback = new Callback($sortCallback);
        $template = $callback->getArgumentsCount() == 4 ?
            [Argument::KEY(), Argument::ITEM(), Argument::SECOND_KEY(), Argument::SECOND_ITEM()] :
            [Argument::ITEM(), Argument::SECOND_ITEM()];
        $callback->setArgumentTemplate($template);

        $mapped = $this
            ->map(function ($k, $v) {
                return [$k, $v];
            })
            ->resetKeys()
            ->toArray();

        uasort(
            $mapped,
            function ($a, $b) use ($callback) {
                $templateArguments = [
                    Argument::KEY => $a[0],
                    Argument::ITEM => $a[1],
                    Argument::SECOND_KEY => $b[0],
                    Argument::SECOND_ITEM => $b[1],
                ];

                return $callback->execute($templateArguments);
            }
        );

        $this->input = (new Collection($mapped))->map(function ($v) {
            yield $v[0];
            yield $v[1];
        });
    }
}
