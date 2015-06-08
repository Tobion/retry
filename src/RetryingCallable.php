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
     * Actual number of retries.
     *
     * @var int
     */
    private $retries = 0;

    /**
     * The callback to execute when an exception is caught.
     *
     * @var callable
     */
    private $exceptionHandler;

    /**
     * Constructor to wrap a callable operation.
     *
     * @param callable $operation        The operation to execute that should be retried on failure
     * @param callable $exceptionHandler A callback to execute when an exception is caught. The callback receives the exception
     *                                   as parameter and can then decide what to do.
     */
    public function __construct(callable $operation, callable $exceptionHandler)
    {
        $this->operation = $operation;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Returns the number of retries used.
     *
     * @return int The number of retries used
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
                call_user_func($this->exceptionHandler, $e);

                $this->retries++;
            }
        } while (true);
    }
}
