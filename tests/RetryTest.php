<?php

namespace Tobion\Retry\Tests;

use PHPUnit\Framework\TestCase;
use Tobion\Retry\Retry;
use Tobion\Retry\RetryConfigurator;
use Tobion\Retry\RetryingCallable;

class RetryTest extends TestCase
{
    public function testConfigure(): void
    {
        $this->assertInstanceOf(RetryConfigurator::class, Retry::configure());
    }

    public function testDecorate(): void
    {
        $this->assertInstanceOf(RetryingCallable::class, Retry::decorate(function () { return 42; }));
    }

    public function testRetryPassesOnArgumentsAndReturnsCallableReturnValue(): void
    {
        $this->assertSame(42, Retry::retry(function (int $arg1, int $arg2) { return $arg1 + $arg2; }, 40, 2));
    }
}
