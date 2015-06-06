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
     * @param callable $operation The operation to execute
     *
     * @return mixed The return value of the passed operation
     */
    public static function retry(callable $operation)
    {
        return self::createBuilder()->retry($operation);
    }
}
