<?php

namespace Knapsack\Callback;

use Knapsack\Collection;
use ReflectionParameter;

class CallbackArguments
{
    /**
     * @var CallbackArgument[]
     */
    private $arguments;

    /**
     * @param ReflectionParameter[] $reflectionParameters
     */
    public function __construct(array $reflectionParameters)
    {
        $this->arguments = [];

        foreach ($reflectionParameters as $reflectionParameter) {
            $this->arguments[] = new CallbackArgument($reflectionParameter);
        }
    }

    public function resolve(array $variables = [])
    {
        $result = [];

        foreach ($this->arguments as $argument) {
            $value = $argument->getValue();

            if ($value instanceof Argument) {
                $value = $variables[$value->type()];

                if ($argument->getClassName() == Collection::class) {
                    $value = new Collection($value);
                }
            }

            $result[] = $value;
        }

        return $result;
    }

    public function applyTemplate(array $templateArguments)
    {
        //todo: sizes must match
        for ($i = 0; $i < count($templateArguments) && $i < count($this->arguments); $i++) {
            $this->arguments[$i]->setValue($templateArguments[$i]);
        }
    }
}
