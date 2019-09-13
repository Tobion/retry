<?php

namespace Tobion\Retry;

/**
 * Simple retry entry point providing shortcuts.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
final class Retry
{
    /**
     * Returns a builder to configure custom retry logic.
     */
    public static function configure(): RetryConfigurator
    {
        return new RetryConfigurator();
    }

    /**
     * Returns a callable that decorates the given operation that should be retried on failure.
     */
    public static function decorate(callable $callable): RetryingCallable
    {
        return (new RetryConfigurator())->decorate($callable);
    }

    /**
     * Executes the passed callable and its arguments with the default retry behavior.
     *
     * @see RetryConfigurator
     *
     * @return mixed The return value of the passed callable
     */
    public static function call(callable $callable, ...$arguments)
    {
        return (new RetryConfigurator())->call($callable, ...$arguments);
    }

    /**
     * This class cannot be instantiated.
     */
    private function __construct()
    {
    }
}
