<?php

namespace Tobion\Retry\ExceptionHandler;

/**
 * Invokable class that limits the number of ignored exceptions to a configured maximum.
  *
 * @author Tobias Schultze <http://tobion.de>
 */
class IgnoreUntilMaxReached
{
    /**
     * Number of exception occurrences that are still allowed.
     *
     * @var int
     */
    private $leftOccurrences;

    /**
     * Constructor.
     *
     * @param int $maxOccurrences Maximum number of exceptions that are ignored
     */
    public function __construct($maxOccurrences)
    {
        $this->leftOccurrences = $maxOccurrences;
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
        if ($this->leftOccurrences > 0) {
            $this->leftOccurrences--;
        } else {
            // Too many exceptions, rethrow last caught exception
            throw $e;
        }
    }
}
