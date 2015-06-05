<?php

namespace Tobion\Retry\Tests;

use Tobion\Retry\Retry;

/**
 * Retry tests
 *
 * @author Tobias Schultze <http://tobion.de>
 * @author Christian Riesen <http://christianriesen.com>
 */
class RetryTest extends \PHPUnit_Framework_TestCase
{
    private $retryableException;

    public function setUp()
    {
        $this->retryableException = __NAMESPACE__ . '\RetryableException';
    }

    public function testConstructor()
    {
        $retry = new Retry(
            function () {
                return 42;
            }
        );

        $this->assertNull($retry->getRetries());
    }

    public function testWithoutRetry()
    {
        $retry = new Retry($this->getCallable(0));

        $this->assertSame('return-value', $retry());
        $this->assertSame(0, $retry->getRetries(), 'Callable must be executed without retries if no exceptions happen');
    }

    public function testExecuteOnceWithZeroMaxRetries()
    {
        $retry = new Retry($this->getCallable(0), $this->retryableException, 0);

        $this->assertSame('return-value', $retry(), 'Callable must be executed when max retries is 0');
        $this->assertSame(0, $retry->getRetries());
    }

    public function testExecuteOnceWithNegativeMaxRetries()
    {
        $retry = new Retry($this->getCallable(0), $this->retryableException, -3);

        $this->assertSame('return-value', $retry(), 'Callable must be executed when max retries is negative');
        $this->assertSame(0, $retry->getRetries());
    }

    public function testRetrySucceedsWithDefaultMaxRetries()
    {
        $retry = new Retry($this->getCallable(2), $this->retryableException);

        $this->assertSame('return-value', $retry());
        $this->assertSame(2, $retry->getRetries());
    }

    public function testRetrySucceedsWithTwoRetryableExceptions()
    {
        $exceptions = [$this->retryableException, 'RuntimeException'];

        $retry = new Retry(
            [new RetryCallableExample(2), 'useTwoExceptions'],
            $exceptions,
            5
        );

        $this->assertSame('return-value', $retry());
        $this->assertSame(2, $retry->getRetries());
    }

    public function testRetryFailsAfterMaxRetries()
    {
        $retry = new Retry($this->getCallable(2), $this->retryableException, 1);

        try {
            $retry();
            $this->fail('Wrapper should rethrow exception when max retries has been reached.');
        } catch (RetryableException $e) {
            $this->assertSame('Retryable error', $e->getMessage());
            $this->assertSame(1, $retry->getRetries());
        }
    }

    public function testNoRetryOnGenericError()
    {
        $retry = new Retry(
            [new RetryCallableExample(), 'retryableErrorFollowedByGenericError'],
            $this->retryableException,
            5
        );

        try {
            $retry();
            $this->fail('Wrapper should rethrow exception when not retryable.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Generic error', $e->getMessage());
            $this->assertSame(1, $retry->getRetries(), 'One retry, then abort');
        }
    }

    public function testExceptionCallback()
    {
        $retry = new Retry($this->getCallable(1), $this->retryableException, 1, function (RetryableException $e) {
            $this->assertSame('Retryable error', $e->getMessage(), 'Callback receives caught exception as argument');

            throw new \RuntimeException('Exception from callback');
        });

        try {
            $retry();
            $this->fail('A failed exception callback should make the retry fail');
        } catch (\RuntimeException $e) {
            $this->assertSame('Exception from callback', $e->getMessage(), 'Callback exception is passed on');
            $this->assertSame(0, $retry->getRetries(), 'A failed exception callback does not increase the retry counter');
        }
    }

    public function testInvokeWithParams()
    {
        $retry = new Retry(__NAMESPACE__ . '\RetryCallableExample::staticMethodWithParams');

        $this->assertSame('foobar', $retry('foo', 'bar'));
    }

    /**
     * Get callable for testing
     *
     * @param int $succeedAfterCalls How many times does it fail before suceeding?
     *
     * @return callable
     */
    private function getCallable($succeedAfterCalls = 0)
    {
        $callable = new RetryCallableExample($succeedAfterCalls);

        return [$callable, 'succeedAsConfigured'];
    }
}

class RetryableException extends \Exception
{
}

class RetryCallableExample
{
    private $succeedAfterCalls = 0;
    private $executionCount = 0;

    public function __construct($succeedAfterCalls = 0)
    {
        $this->succeedAfterCalls = $succeedAfterCalls;
    }

    public function succeedAsConfigured()
    {
        $this->executionCount++;

        if ($this->executionCount > $this->succeedAfterCalls) {
            return 'return-value';
        }

        throw new RetryableException('Retryable error');
    }

    public function retryableErrorFollowedByGenericError()
    {
        $this->executionCount++;

        if ($this->executionCount > 1) {
            throw new \RuntimeException('Generic error');
        }

        throw new RetryableException('Retry this');

    }

    public function useTwoExceptions()
    {
        $this->executionCount++;

        if ($this->executionCount > $this->succeedAfterCalls) {
            return 'return-value';
        }

        if ($this->executionCount % 2) {
            throw new \RuntimeException('Runtime error');
        }

        throw new RetryableException('Retryable error');
    }

    public static function staticMethodWithParams($param1, $param2)
    {
        return $param1 . $param2;
    }
}


