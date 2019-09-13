<?php

namespace Tobion\Retry;

use Tobion\Retry\ExceptionHandler\DelayMilliseconds;
use Tobion\Retry\ExceptionHandler\DelegatingStack;
use Tobion\Retry\ExceptionHandler\RethrowNonRetryableExceptions;
use Tobion\Retry\ExceptionHandler\RethrowOnMaxRetries;

/**
 * Builder for configuring the retry logic.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
class RetryConfigurator
{
    /**
     * @var string[]
     */
    private $retryableExceptionClasses = [];

    /**
     * @var int
     */
    private $maxRetries;

    /**
     * @var int
     */
    private $delayInMs;

    /**
     * Configures the retry logic. By default:
     *
     * - The callable is retried twice (i.e. max three executions). If it still fails, the last error is rethrown.
     * - Retries have a 300 milliseconds delay between them.
     * - Every \Throwable will trigger the retry logic, i.e. both exceptions and errors.
     */
    public function __construct(int $maxRetries = 2, int $delayInMs = 300, string $exceptionToRetry = \Throwable::class)
    {
        $this->maxRetries = $maxRetries;
        $this->delayInMs = $delayInMs;
        $this->setRetryableExceptions($exceptionToRetry);
    }

    /**
     * Sets the exception classes/interfaces to catch and retry on.
     *
     * The callable is only re-executed for exceptions that are a subclass of one of the configured exceptions. Other exceptions will be ignored
     * and just bubble upwards immediately.
     *
     * For example, for handling database deadlocks and timeouts with Doctrine, it makes sense to configure `\Doctrine\DBAL\Exception\RetryableException`.
     *
     * @return $this
     */
    public function setRetryableExceptions(string $exceptionClass, string ...$moreExceptionClasses): self
    {
        array_unshift($moreExceptionClasses, $exceptionClass);
        $this->retryableExceptionClasses = $moreExceptionClasses;

        return $this;
    }

    /**
     * Sets the maximum number of retries.
     *
     * @return $this
     */
    public function setMaxRetries(int $maxRetries): self
    {
        $this->maxRetries = $maxRetries;

        return $this;
    }

    /**
     * Sets the delay between retries in milliseconds.
     *
     * Set to zero to disable delay.
     *
     * @return $this
     */
    public function setDelayInMs(int $milliseconds): self
    {
        $this->delayInMs = $milliseconds;

        return $this;
    }

    /**
     * Returns the exception classes/interfaces to catch and retry on.
     *
     * If empty, every exception will trigger the retry logic.
     *
     * @return string[]
     */
    public function getRetryableExceptions(): array
    {
        return $this->retryableExceptionClasses;
    }

    /**
     * Returns the maximum number of retries.
     */
    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    /**
     * Returns the delay between retries in milliseconds.
     */
    public function getDelayInMs(): int
    {
        return $this->delayInMs;
    }

    /**
     * Returns a callable that decorates the given operation that should be retried on failure.
     */
    public function decorate(callable $operation): RetryingCallable
    {
        $handlers = [];

        // we can skip the handler in this default case
        if ([\Throwable::class] !== $this->retryableExceptionClasses) {
            $handlers[] = new RethrowNonRetryableExceptions(...$this->retryableExceptionClasses);
        }

        $handlers[] = new RethrowOnMaxRetries($this->maxRetries);

        if ($this->delayInMs > 0) {
            $handlers[] = new DelayMilliseconds($this->delayInMs);
        }

        return new RetryingCallable($operation, new DelegatingStack(...$handlers));
    }

    /**
     * Executes the passed callable and its arguments with the configured retry behavior.
     *
     * @return mixed The return value of the passed callable
     */
    public function retry(callable $operation, ...$arguments)
    {
        $retryingCallable = $this->decorate($operation);

        return $retryingCallable(...$arguments);
    }
}
