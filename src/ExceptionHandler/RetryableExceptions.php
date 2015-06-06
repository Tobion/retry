<?php

namespace Tobion\Retry\ExceptionHandler;

/**
 * Invokable class that will rethrow the caught exception unless it a subclass of one of the configured exceptions.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
class RetryableExceptions
{
    /**
     * Exceptions.
     *
     * @var array
     */
    private $exceptions;

    /**
     * Constructor.
     *
     * @param string[] $exceptions Array of exception classes/interfaces
     */
    public function __construct(array $exceptions)
    {
        $this->exceptions = $exceptions;
    }

    /**
     * It rethrows the caught exception unless it a subclass of one of the configured exceptions.
     *
     * @param \Exception $e The caught exception
     *
     * @throws \Exception The caught exception
     */
    public function __invoke(\Exception $e)
    {
        foreach ($this->exceptions as $retryableException) {
            if ($e instanceof $retryableException) {
                return;
            }
        }

        throw $e;
    }
}
