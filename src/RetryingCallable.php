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
class RetryingCallable
{
    /**
     * The operation to execute that can be retried on failure.
     *
     * @var callable
     */
    private $operation;

    /**
     * An array of callbacks to execute when an exception is caught.
     *
     * @var callable[]
     */
    private $exceptionHandlers = [];

    /**
     * Constructor to wrap a callable operation.
     *
     * @param callable   $operation         The operation to execute that should be retried on failure
     * @param callable[] $exceptionHandlers An array of callbacks to execute when an exception is caught.
     *                                      Each callback receives the exception as parameter and can then decide what to do.
     */
    public function __construct(callable $operation, array $exceptionHandlers)
    {
        $this->operation = $operation;
        $this->exceptionHandlers = $exceptionHandlers;
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
        $args = func_get_args();

        do {
            try {
                return call_user_func_array($this->operation, $args);
            } catch (\Exception $e) {
                foreach ($this->exceptionHandlers as $callable) {
                    call_user_func($callable, $e);
                }
            }
        } while (true);
    }
}
