<?php

namespace Tobion\Retry\ExceptionHandler;

/**
 * Invokable class that counts the number of invocations and will rethrow the exception when max retries is reached.
  *
 * @author Tobias Schultze <http://tobion.de>
 */
class MaxRetries
{
    /**
     * Maximum number of retries.
     *
     * @var int
     */
    private $maxRetries;

    /**
     * Actual number of retries.
     *
     * @var int
     */
    private $retries = 0;

    /**
     * Actual number of retries.
     *
     * @var int|null
     */
    private $lastRetries;

    /**
     * Constructor.
     *
     * @param int $maxRetries Maximum number of retries
     */
    public function __construct($maxRetries)
    {
        $this->maxRetries = $maxRetries;
    }

    /**
     * Returns the number of retries used.
     *
     * @return int|null The number of retries used or null if wrapper has not been invoked yet
     */
    public function getLastRetries()
    {
        return $this->lastRetries;
    }

    /**
     * Counts the number of invocations and will rethrow the exception when max retries is reached.
     *
     * @param \Exception $e The caught exception
     *
     * @throws \Exception The caught exception
     */
    public function __invoke(\Exception $e)
    {
        if ($this->retries < $this->maxRetries) {
            $this->retries++;
        } else {
            // Too many retries, rethrow last caught exception
            $this->lastRetries = $this->retries;
            $this->retries = 0;

            throw $e;
        }
    }
}
