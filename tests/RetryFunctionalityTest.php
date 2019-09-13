<?php

namespace Tobion\Retry\Tests;

use PHPUnit\Framework\TestCase;
use Tobion\Retry\RetryConfigurator;

class RetryFunctionalityTest extends TestCase
{
    public function testWithoutRetry(): void
    {
        $retry = (new RetryConfigurator())->decorate($this->getCallable(0));

        $this->assertSame(TestExamplesToRetry::RETURN_VALUE, $retry());
        $this->assertSame(0, $retry->getRetries(), 'Callable must be executed without retries if no exceptions happen');
    }

    public function testExecuteOnceWithZeroMaxRetries(): void
    {
        $retry = (new RetryConfigurator(0))->decorate($this->getCallable(0));

        $this->assertSame(TestExamplesToRetry::RETURN_VALUE, $retry(), 'Callable must be executed when max retries is 0');
        $this->assertSame(0, $retry->getRetries());
    }

    public function testExecuteOnceWithNegativeMaxRetries(): void
    {
        $retry = (new RetryConfigurator(-3))->decorate($this->getCallable(0));

        $this->assertSame(TestExamplesToRetry::RETURN_VALUE, $retry(), 'Callable must be executed when max retries is negative');
        $this->assertSame(0, $retry->getRetries());
    }

    public function testRetrySucceeds(): void
    {
        $retry = (new RetryConfigurator())->decorate($this->getCallable(2));

        $this->assertSame(TestExamplesToRetry::RETURN_VALUE, $retry());
        $this->assertSame(2, $retry->getRetries());
    }

    public function testRetrySucceedsWithTwoRetryableExceptions(): void
    {
        $retry = (new RetryConfigurator(4))
            ->setRetryableExceptions(TestExceptionToRetry::class,TestDifferentException::class)
            ->decorate([new TestExamplesToRetry(4), 'useTwoExceptions'])
        ;

        $this->assertSame(TestExamplesToRetry::RETURN_VALUE, $retry());
        $this->assertSame(4, $retry->getRetries());
    }

    public function testRetryFailsAfterMaxRetries(): void
    {
        $retry = (new RetryConfigurator(1))->decorate($this->getCallable(2));

        try {
            $retry();
            $this->fail('Wrapper should rethrow exception when max retries has been reached.');
        } catch (TestExceptionToRetry $e) {
            $this->assertSame('Retryable error', $e->getMessage());
            $this->assertSame(1, $retry->getRetries());
        }
    }

    public function testNoRetryWhenNotRetryableError(): void
    {
        $retry = (new RetryConfigurator(10, 300, TestExceptionToRetry::class))->decorate(
            [new TestExamplesToRetry(), 'retryableErrorFollowedByOtherError']
        );

        try {
            $retry();
            $this->fail('Wrapper should rethrow exception when not retryable.');
        } catch (\Error $e) {
            $this->assertSame('Different error', $e->getMessage());
            $this->assertSame(1, $retry->getRetries(), 'One retry, then abort because not configured as retryable');
        }
    }

    public function testRetryDelays(): void
    {
        $start = microtime(true);

        $returnValue = (new RetryConfigurator(2, 500))->retry($this->getCallable(2));

        $elapsedTimeInMs = (microtime(true) - $start) * 1000;

        $this->assertSame(TestExamplesToRetry::RETURN_VALUE, $returnValue);
        $this->assertGreaterThan(800, $elapsedTimeInMs, 'With delay it should take about 1000 ms (2 retries * 500 ms)');
    }

    public function testInvokeWithParams(): void
    {
        $retry = (new RetryConfigurator())->decorate(TestExamplesToRetry::class.'::staticMethodWithParams');

        $this->assertSame('foobar', $retry('foo', 'bar'));
    }

    private function getCallable(int $succeedAfterCalls = 0): callable
    {
        $callable = new TestExamplesToRetry($succeedAfterCalls);

        return [$callable, 'succeedAsConfigured'];
    }
}

interface TestRetryableException extends \Throwable
{
}

class TestExceptionToRetry extends \Exception implements TestRetryableException
{
}

class TestDifferentException extends \Exception
{
}

class TestExamplesToRetry
{
    public const RETURN_VALUE = 'return-value';

    private $succeedAfterCalls = 0;
    private $executionCount = 0;

    public function __construct(int $succeedAfterCalls = 0)
    {
        $this->succeedAfterCalls = $succeedAfterCalls;
    }

    public function succeedAsConfigured(): string
    {
        $this->executionCount++;

        if ($this->executionCount > $this->succeedAfterCalls) {
            return self::RETURN_VALUE;
        }

        throw new TestExceptionToRetry('Retryable error');
    }

    public function retryableErrorFollowedByOtherError(): void
    {
        $this->executionCount++;

        if ($this->executionCount > 1) {
            throw new \Error('Different error');
        }

        throw new TestExceptionToRetry('Retry this');
    }

    public function useTwoExceptions(): string
    {
        $this->executionCount++;

        if ($this->executionCount > $this->succeedAfterCalls) {
            return self::RETURN_VALUE;
        }

        if ($this->executionCount % 2) {
            throw new TestDifferentException('Different error');
        }

        throw new TestExceptionToRetry('Retryable error');
    }

    public static function staticMethodWithParams(string $param1, string $param2): string
    {
        return $param1 . $param2;
    }
}


