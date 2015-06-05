<?php

namespace Tobion\Retry;

/**
 * Wraps an operation, represented as a callable, in retry logic.
 *
 * The class implements the retry logic for you by re-executing your callable
 * in case of temporary errors where retrying the failed operation, after a
 * short delay usually resolves the problem. Just wrap your operation in this
 * class and invoke it. You can also pass arguments when invoking the wrapper
 * which will be passed through to the underlying callable.
 *
 * @author Tobias Schultze <http://tobion.de>
 * @author Christian Riesen <http://christianriesen.com>
 */
class Retry
{
    /**
     * The operation to execute that can be retried on failure.
     *
     * @var callable
     */
    private $operation;

    /**
     * Maximum number of retries.
     *
     * @var int
     */
    private $maxRetries;

    /**
     * Actual number of retries.
     *
     * @var int|null
     */
    private $retries;

    /**
     * Exceptions to catch and retry on.
     *
     * @var string[]
     */
    private $exceptions = [];

    /**
     * Callback when an exception is caught.
     *
     * @var callable
     */
    private $exceptionCallback;

    /**
     * Constructor to wrap a callable operation.
     *
     * @param callable        $operation         The operation to execute that should be retried on failure
     * @param string|string[] $exceptions        Exceptions to catch and retry on (by default every exception)
     * @param int             $maxRetries        Maximum number of retries
     * @param callable|null   $exceptionCallback The callback to execute when an exception is caught and the operation is about to be retried.
     *                                           By default, it delays retries by 300 milliseconds. The callback receives the exception as parameter.
     */
    public function __construct(callable $operation, $exceptions = 'Exception', $maxRetries = 2, callable $exceptionCallback = null)
    {
        $this->operation = $operation;
        $this->exceptions = (array) $exceptions;
        $this->maxRetries = $maxRetries;
        $this->exceptionCallback = $exceptionCallback ?: new DelayMilliseconds(300);
    }

    /**
     * Returns the number of retries used.
     *
     * @return int|null The number of retries used or null if wrapper has not been invoked yet
     */
    public function getRetries()
    {
        return $this->retries;
    }

    /**
     * Executes the wrapped callable and retries it in case of a configured exception happening.
     *
     * The callable is only re-executed for exceptions that are a subclass of one of the configured exceptions. Other exceptions will be ignored
     * and just bubble upwards immediately.
     *
     * All arguments given will be passed through to the wrapped callable.
     *
     * @return mixed The return value of the wrapped callable
     *
     * @throws \Exception When retries are exceeded or retry is not configured for it
     */
    public function __invoke()
    {
        $this->retries = 0;
        $args = func_get_args();

        do {
            try {
                return call_user_func_array($this->operation, $args);
            } catch (\Exception $e) {
                // Catching all, then checking what exception it is
                $found = false;

                foreach ($this->exceptions as $retryableException) {
                    if ($e instanceof $retryableException) {
                        $found = true;
                        break;
                    }
                }

                // Not a retryable exception, throw again
                if (!$found) {
                    throw $e;
                }

                if ($this->retries < $this->maxRetries) {
                    // Haven't exceeded retry count yet, so execute callback with exception as argument
                    // This could add some retry delay or do other custom logic like logging
                    call_user_func($this->exceptionCallback, $e);

                    $this->retries++;
                } else {
                    // Too many retries, rethrow last caught exception
                    throw $e;
                }
            }
        } while (true);
    }
}
