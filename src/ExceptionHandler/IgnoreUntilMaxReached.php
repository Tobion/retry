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
     * Maximum number of exception occurrences.
     *
     * @var int
     */
    private $maxOccurrences;

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
        $this->leftOccurrences = $this->maxOccurrences = $maxOccurrences;
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
            // Reset to original state in case the exception is caught, so it starts from the beginning.
            $this->leftOccurrences = $this->maxOccurrences;

            // Too many exceptions, rethrow last caught exception
            throw $e;
        }
    }
}
