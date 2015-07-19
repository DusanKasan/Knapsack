<?php

namespace Knapsack\Callback;

use Knapsack\Collection;
use ReflectionParameter;

class CallbackArgument
{
    /**
     * @var ReflectionParameter
     */
    private $reflectionParameter;

    /**
     * @var bool
     */
    private $hasDefaultValue = false;

    /**
     * @var mixed
     */
    private $defaultValue;

    public function __construct(ReflectionParameter $reflectionParameter)
    {
        $this->reflectionParameter = $reflectionParameter;

        if ($reflectionParameter->isDefaultValueAvailable()) {
            $this->hasDefaultValue = true;
            $this->defaultValue = $reflectionParameter->getDefaultValue();
        }
    }

    public function hasValue()
    {
        return $this->hasDefaultValue;
    }

    public function getValue()
    {
        return $this->defaultValue;
    }

    public function setValue($value)
    {
        $class = $this->reflectionParameter->getClass();
        if (
            $class &&
            $class->getName() == Collection::class &&
            !($value instanceof Argument)
        ) {
            $value = new Collection($value);
        }

        $this->hasDefaultValue = true;
        $this->defaultValue = $value;
    }

    public function getClassName()
    {
        return $this->reflectionParameter->getClass() ?
            $this->reflectionParameter->getClass()->getName() :
            null;
    }
}
