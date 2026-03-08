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
final class RetryConfigurator
{
    /**
     * @var class-string<\Throwable>[]
     */
    private array $retryableExceptionClasses = [];

    /**
     * @var int<0,max>
     */
    private int $maxRetries;

    /**
     * @var int<0,max>
     */
    private int $delayInMs = 0;

    /**
     * Configures the retry logic. By default:
     *
     * - The callable is retried twice (i.e. max three executions). If it still fails, the last error is rethrown.
     * - Retries have a no delay between them.
     * - Every \Throwable will trigger the retry logic, i.e. both \Exception and \Error.
     *
     * @param int<0,max> $maxRetries
     */
    public function __construct(int $maxRetries = 2)
    {
        $this->maxRetries = $maxRetries;
    }

    /**
     * Sets the exception classes/interfaces to catch and retry on.
     *
     * The callable is only re-executed for exceptions that are a subclass of one of the configured exceptions. Other exceptions will be ignored
     * and just bubble upwards immediately.
     *
     * For example, for handling database deadlocks and timeouts with Doctrine, it makes sense to configure `\Doctrine\DBAL\Exception\RetryableException`.
     *
     * @param class-string<\Throwable> $exceptionClass
     * @param class-string<\Throwable> ...$moreExceptionClasses
     *
     * @return $this
     */
    public function retryOnSpecificExceptions(string $exceptionClass, string ...$moreExceptionClasses): self
    {
        array_unshift($moreExceptionClasses, $exceptionClass);
        $this->retryableExceptionClasses = $moreExceptionClasses;

        return $this;
    }

    /**
     * Sets the maximum number of retries.
     *
     * @param int<0,max> $maxRetries
     *
     * @return $this
     */
    public function maxRetries(int $maxRetries): self
    {
        $this->maxRetries = $maxRetries;

        return $this;
    }

    /**
     * Sets the delay between retries in milliseconds.
     *
     * Set to zero to disable delay.
     *
     * @param int<0,max> $milliseconds
     *
     * @return $this
     */
    public function delayInMs(int $milliseconds): self
    {
        $this->delayInMs = $milliseconds;

        return $this;
    }

    /**
     * Returns a callable that decorates the given operation that should be retried on failure.
     *
     * @template TResult
     *
     * @param callable():TResult $operation
     *
     * @return RetryingCallable<TResult>
     */
    public function decorate(callable $operation): RetryingCallable
    {
        $handlers = [];

        // we can skip the handler in this default case
        if ([] !== $this->retryableExceptionClasses && [\Throwable::class] !== $this->retryableExceptionClasses) {
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
     * @template TResult
     *
     * @param callable():TResult $operation
     *
     * @return TResult The return value of the passed callable
     */
    public function call(callable $operation, mixed ...$arguments): mixed
    {
        $retryingCallable = $this->decorate($operation);

        return $retryingCallable(...$arguments);
    }
}
