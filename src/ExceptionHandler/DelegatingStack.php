<?php

namespace Tobion\Retry\ExceptionHandler;

/**
 * Invokable class that delegates exception handling to an array of callables.
  *
 * @author Tobias Schultze <http://tobion.de>
 */
class DelegatingStack
{
    /**
     * An array of callbacks to execute when an exception is caught.
     *
     * @var callable[]
     */
    private $exceptionHandlers = [];

    /**
     * Constructor.
     *
     * @param callable[] $exceptionHandlers An array of callbacks to execute when an exception is caught.
     */
    public function __construct(array $exceptionHandlers)
    {
        $this->exceptionHandlers = $exceptionHandlers;
    }

    /**
     * Delegates handling to the array of configured exception handlers.
     *
     * @param \Exception $e The caught exception
     *
     * @throws \Exception The caught exception
     */
    public function __invoke(\Exception $e)
    {
        foreach ($this->exceptionHandlers as $callable) {
            call_user_func($callable, $e);
        }
    }
}
