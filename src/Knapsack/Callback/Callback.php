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

        $template = $argumentTemplate ?: $this->guessTemplate($this->arguments);
        $this->arguments->applyTemplate($template);
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
        return $this->getArgumentsCount() == 1 ?
            [Argument::item()] :
            [Argument::key(), Argument::item()];
    }

    /**
     * @return int
     */
    public function getArgumentsCount()
    {
        return $this->arguments->count();
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    public function executeWithKeyAndValue($key, $value)
    {
        $templateVariables = [
            Argument::KEY => $key,
            Argument::ITEM => $value,
        ];

        return $this->execute($templateVariables);
    }

    /**
     * @param array $templateVariables
     * @return mixed
     */
    public function execute(array $templateVariables = [])
    {
        $arguments = $this->arguments->resolve($templateVariables);

        return call_user_func_array($this->callback, $arguments);
    }

    /**
     * @param array $template
     */
    public function setArgumentTemplate(array $template)
    {
        $this->arguments->applyTemplate($template);
    }
}
