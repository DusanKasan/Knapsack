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

    /**
     * @return bool
     */
    public function hasValue()
    {
        return $this->hasDefaultValue;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {

        $class = $this->reflectionParameter->getClass();
        if (
            $class &&
            $class->name == Collection::class &&
            !($value instanceof Argument)
        ) {
            $value = new Collection($value);
        }

        $this->hasDefaultValue = true;
        $this->defaultValue = $value;
    }

    /**
     * @return null|string
     */
    public function getClassName()
    {
        return $this->reflectionParameter->getClass() ?
            $this->reflectionParameter->getClass()->name :
            null;
    }
}
