<?php

namespace Tobion\Retry;

use Tobion\Retry\ExceptionHandler\RetryableExceptions;
use Tobion\Retry\ExceptionHandler\MaxRetries;
use Tobion\Retry\ExceptionHandler\DelayMilliseconds;

/**
 * Builder for configuring the retry logic.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
class RetryingCallableBuilder
{
    /**
     * Exceptions to catch and retry on.
     *
     * @var string[]
     */
    private $exceptions = [];

    /**
     * Maximum number of retries.
     *
     * @var int
     */
    private $maxRetries = 2;

    /**
     * Delay between retries in milliseconds.
     *
     * @var int
     */
    private $retryDelay = 300;

    /**
     * Sets the exceptions to catch and retry on.
     *
     * The callable is only re-executed for exceptions that are a subclass of one of the configured exceptions. Other exceptions will be ignored
     * and just bubble upwards immediately. By default, every exception will trigger the retry logic.
     *
     * @param string|string[] $exceptions One or multiple exception classes/interfaces
     *
     * @return self Fluent interface
     */
    public function setRetryableExceptions($exceptions)
    {
        $this->exceptions = (array) $exceptions;

        return $this;
    }

    /**
     * Sets the maximum number of retries.
     *
     * By default, the operation is retried twice. So a maximum of three executions.
     *
     * @param int $maxRetries Maximum number of retries
     *
     * @return self Fluent interface
     */
    public function setMaxRetries($maxRetries)
    {
        $this->maxRetries = $maxRetries;

        return $this;
    }

    /**
     * Sets the delay between retries in milliseconds.
     *
     * By default, 300 milliseconds delay are configured.
     *
     * @param int $retryDelay Delay in milliseconds
     *
     * @return self Fluent interface
     */
    public function setRetryDelay($retryDelay)
    {
        $this->retryDelay = $retryDelay;

        return $this;
    }

    /**
     * Returns a callable that decorates the given operation to add the retry logic.
     *
     * @param callable $operation The operation that should be retried on failure
     *
     * @return callable The retrying callable
     */
    public function getDecorator(callable $operation)
    {
        $handlers = [];

        if ($this->exceptions) {
            $handlers[] = new RetryableExceptions($this->exceptions);
        }

        $handlers[] = new MaxRetries($this->maxRetries);

        if ($this->retryDelay > 0) {
            $handlers[] = new DelayMilliseconds($this->retryDelay);
        }

        return new RetryingCallable($operation, $handlers);
    }

    /**
     * Executes the passed callable with the configured retry behavior.
     *
     * @param callable $operation The operation to execute
     *
     * @return mixed The return value of the passed operation
     */
    public function retry(callable $operation)
    {
        $retryingCallable = $this->getDecorator($operation);

        return $retryingCallable();
    }
}
