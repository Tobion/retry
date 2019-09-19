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
}
