<?php

namespace Tobion\Retry;

use Doctrine\DBAL\Exception\RetryableException;

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
     * Executes the passed callable and its arguments with the a preconfigured retry behavior suitable for Doctrine database transactions.
     *
     * @return mixed The return value of the passed callable
     */
    public static function onDoctrineExceptionWith2Retries300MsDelay(callable $callable, ...$arguments)
    {
        return self::configure()
            ->maxRetries(2)
            ->delayInMs(300)
            ->retryOnSpecificExceptions(RetryableException::class)
            ->call($callable, ...$arguments);
    }

    /**
     * This class cannot be instantiated.
     */
    private function __construct()
    {
    }
}
