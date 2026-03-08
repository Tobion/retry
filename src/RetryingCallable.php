<?php

namespace Tobion\Retry;

/**
 * Wraps an operation, represented as a callable, in retry logic.
 *
 * @author Tobias Schultze <http://tobion.de>
 * @author Christian Riesen <http://christianriesen.com>
 *
 * @template TResult
 */
final class RetryingCallable
{
    /**
     * @var callable():TResult
     */
    private $operation;

    /**
     * @var int<0,max>
     */
    private int $retries = 0;

    /**
     * @var callable(\Throwable):void
     */
    private $exceptionHandler;

    /**
     * Constructor to wrap a callable operation.
     *
     * @param callable():TResult        $operation        The operation to execute that should be retried on failure
     * @param callable(\Throwable):void $exceptionHandler A callback to execute when an exception is caught. The callback receives the exception
     *                                                    as parameter and can then decide what to do.
     */
    public function __construct(callable $operation, callable $exceptionHandler)
    {
        $this->operation = $operation;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Returns the number of retries used.
     *
     * @return int<0,max>
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
     * @return TResult The return value of the wrapped callable
     *
     * @throws \Throwable When the exception handler also throws an exception.
     */
    public function __invoke(mixed ...$arguments): mixed
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
