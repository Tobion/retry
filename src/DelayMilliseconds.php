<?php

namespace Tobion\Retry;

/**
 * Invokable class that delays execution by given number of milliseconds with usleep.
  *
 * @author Tobias Schultze <http://tobion.de>
 * @author Christian Riesen <http://christianriesen.com>
 */
class DelayMilliseconds
{
    /**
     * Delay in milliseconds.
     *
     * @var int
     */
    private $milliseconds;

    /**
     * Constructor.
     *
     * @param int $milliseconds Delay in milliseconds
     */
    public function __construct($milliseconds)
    {
        $this->milliseconds = $milliseconds;
    }

    /**
     * Wait the configured amount of milliseconds.
     *
     * @return void
     */
    public function __invoke()
    {
        if ($this->milliseconds <= 0) {
            return;
        }

        usleep($this->milliseconds * 1000);
    }
}
