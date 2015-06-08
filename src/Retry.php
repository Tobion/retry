<?php

namespace Tobion\Retry;

/**
 * Entry point for the retry logic.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
class Retry
{
    /**
     * Returns a builder to configure custom retry logic.
     *
     * @return RetryingCallableBuilder Builder to configure retry logic
     */
    public static function createBuilder()
    {
        return new RetryingCallableBuilder();
    }

    /**
     * Executes the passed callable with the default retry behavior.
     *
     * Extra arguments are passed to the operation.
     *
     * @param callable $operation The operation to execute
     *
     * @return mixed The return value of the passed operation
     */
    public static function retry(callable $operation)
    {
        $retryingCallable = self::createBuilder()->getDecorator($operation);

        return call_user_func_array($retryingCallable, array_slice(func_get_args(), 1));
    }
}
