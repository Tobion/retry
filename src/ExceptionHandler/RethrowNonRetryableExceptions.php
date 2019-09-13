<?php

namespace Tobion\Retry\ExceptionHandler;

/**
 * Invokable class that will rethrow the caught exception unless it an instance of one of the configured exceptions.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
final class RethrowNonRetryableExceptions
{
    /**
     * @var string[]
     */
    private $exceptions;

    public function __construct(string ...$exceptionClasses)
    {
        $this->exceptions = $exceptionClasses;
    }

    public function __invoke(\Throwable $e): void
    {
        foreach ($this->exceptions as $retryableException) {
            if ($e instanceof $retryableException) {
                return;
            }
        }

        throw $e;
    }
}
