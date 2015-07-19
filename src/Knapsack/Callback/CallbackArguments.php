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

    /**
     * @param array $variables
     * @return array
     */
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

    /**
     * @return int
     */
    public function count()
    {
        return count($this->arguments);
    }

    /**
     * @param array $templateArguments
     */
    public function applyTemplate(array $templateArguments)
    {
        for ($i = 0; $i < count($templateArguments) && $i < count($this->arguments); $i++) {
            $this->arguments[$i]->setValue($templateArguments[$i]);
        }
    }
}
