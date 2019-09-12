<?php

namespace Tobion\Retry\ExceptionHandler;

/**
 * Invokable class that limits the number of retried exceptions to a configured maximum.
 *
 * It counts the number of invocations and will rethrow the exception when max retries is reached.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
final class RethrowOnMaxRetries
{
    /**
     * @var int
     */
    private $maxRetries;

    /**
     * @var int
     */
    private $leftRetries;

    public function __construct(int $maxRetries)
    {
        $this->leftRetries = $this->maxRetries = $maxRetries;
    }

    public function __invoke(\Throwable $e): void
    {
        if ($this->leftRetries > 0) {
            $this->leftRetries--;
        } else {
            // Reset to original state in case the exception is caught, so it starts from the beginning.
            $this->leftRetries = $this->maxRetries;

            // Too many exceptions, rethrow last caught exception
            throw $e;
        }
    }
}
