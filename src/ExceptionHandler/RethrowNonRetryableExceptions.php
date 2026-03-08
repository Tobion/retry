<?php

namespace Tobion\Retry\ExceptionHandler;

/**
 * Invokable class that will rethrow the caught exception unless it an instance of one of the configured exceptions.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
final readonly class RethrowNonRetryableExceptions
{
    /**
     * @var class-string<\Throwable>[]
     */
    private array $exceptionClasses;

    /**
     * @param class-string<\Throwable> ...$exceptionClasses
     */
    public function __construct(string ...$exceptionClasses)
    {
        $this->exceptionClasses = $exceptionClasses;
    }

    public function __invoke(\Throwable $e): void
    {
        foreach ($this->exceptionClasses as $retryableException) {
            if ($e instanceof $retryableException) {
                return;
            }
        }

        throw $e;
    }
}
