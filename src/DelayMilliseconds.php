<?php

namespace Tobion\Retry;

/**
 * Delays execution in milliseconds
  *
 * @author Tobias Schultze <http://tobion.de>
 * @author Christian Riesen <http://christianriesen.com>
 */
class DelayMilliseconds
{
    /**
     * Delay in milliseconds.
     *
     * @var integer
     */
    private $milliseconds;

    /**
     * Constructor to wrap a callable.
     *
     * @param integer $milliseconds Delay in milliseconds
     */
    public function __construct($milliseconds = 300)
    {
        $this->milliseconds = $milliseconds;
    }

    /**
     * Wait the configured amount of milliseconds
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
