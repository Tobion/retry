<?php

namespace Tobion\Retry\ExceptionHandler;

/**
 * Invokable class that delays execution by given number of milliseconds with usleep.
  *
 * @author Tobias Schultze <http://tobion.de>
 * @author Christian Riesen <http://christianriesen.com>
 */
final readonly class DelayMilliseconds
{
    public function __construct(private int $milliseconds)
    {
    }

    public function __invoke(): void
    {
        if ($this->milliseconds <= 0) {
            return;
        }

        usleep($this->milliseconds * 1000);
    }
}
