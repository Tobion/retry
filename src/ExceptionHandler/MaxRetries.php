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
    private $leftRetries;

    /**
     * Constructor.
     *
     * @param int $maxRetries Maximum number of retries
     */
    public function __construct($maxRetries)
    {
        $this->leftRetries = $maxRetries;
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
        if ($this->leftRetries > 0) {
            $this->leftRetries--;
        } else {
            // Too many retries, rethrow last caught exception
            throw $e;
        }
    }
}
