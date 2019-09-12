<?php

namespace Tobion\Retry;

/**
 * Wraps an operation, represented as a callable, in retry logic.
 *
 * @author Tobias Schultze <http://tobion.de>
 * @author Christian Riesen <http://christianriesen.com>
 */
final class RetryingCallable
{
    /**
     * @var callable
     */
    private $operation;

    /**
     * @var int
     */
    private $retries = 0;

    /**
     * @var callable
     */
    private $exceptionHandler;

    /**
     * Constructor to wrap a callable operation.
     *
     * @param callable $operation        The operation to execute that should be retried on failure
     * @param callable $exceptionHandler A callback to execute when an exception is caught. The callback receives the exception
     *                                   as parameter and can then decide what to do.
     */
    public function __construct(callable $operation, callable $exceptionHandler)
    {
        $this->operation = $operation;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Returns the number of retries used.
     */
    public function getRetries(): int
    {
        return $this->retries;
    }

    /**
     * Executes the wrapped callable and retries it until the exception handler also throws an exception.
     *
     * All arguments given will be passed through to the wrapped callable.
     *
     * @return mixed The return value of the wrapped callable
     *
     * @throws \Throwable When the exception handler also throws an exception.
     */
    public function __invoke(...$arguments)
    {
        $this->retries = 0;

        do {
            try {
                return call_user_func_array($this->operation, $arguments);
            } catch (\Throwable $e) {
                call_user_func($this->exceptionHandler, $e);

                $this->retries++;
            }
        } while (true);
    }
}
