<?php

namespace Knapsack\Callback;

use ReflectionFunction;
use ReflectionMethod;

class Callback
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var CallbackArguments
     */
    private $arguments;

    public function __construct(callable $callback, array $argumentTemplate = [])
    {
        $this->callback = $callback;
        $this->arguments = new CallbackArguments($this->getArguments($callback));

        if (!empty($argumentTemplate)) {
            $this->arguments->applyTemplate($argumentTemplate);
        } else {
            $guessedTemplate = $this->guessTemplate($this->arguments);
            $this->arguments->applyTemplate($guessedTemplate);
        }
    }

    public function executeWithKeyAndValue($key, $value)
    {
        $input = [
            Argument::KEY => $key,
            Argument::ITEM => $value,
        ];

        $arguments = $this->arguments->resolve($input);

        return call_user_func_array($this->callback, $arguments);
    }

    /**
     * @param callable $callback
     * @return \ReflectionParameter[]
     */
    private function getArguments(callable $callback)
    {
        if (is_array($callback) && count($callback) == 2) {
            return (new ReflectionMethod($callback[0], $callback[1]))->getParameters();
        } else {
            return (new ReflectionFunction($callback))->getParameters();
        }
    }

    /**
     * @param CallbackArguments $arguments
     * @return array
     */
    private function guessTemplate(CallbackArguments $arguments)
    {
        $argumentCount = count($this->getArguments($this->callback)); //todo: add count method
        return $argumentCount == 1 ? [Argument::ITEM()] : [Argument::KEY(), Argument::ITEM()];
    }
}
