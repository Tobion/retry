<?php

namespace Tobion\Retry\ExceptionHandler;

/**
 * Invokable class that delegates exception handling to an array of callables.
  *
 * @author Tobias Schultze <http://tobion.de>
 */
final class DelegatingStack
{
    /**
     * @var callable[]
     */
    private $exceptionHandlers = [];

    public function __construct(callable ...$exceptionHandlers)
    {
        $this->exceptionHandlers = $exceptionHandlers;
    }

    public function __invoke(\Throwable $e): void
    {
        foreach ($this->exceptionHandlers as $callable) {
            $callable($e);
        }
    }
}
