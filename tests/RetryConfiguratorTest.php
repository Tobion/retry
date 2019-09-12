<?php

namespace Tobion\Retry\Tests;

use PHPUnit\Framework\TestCase;
use Tobion\Retry\RetryConfigurator;

class RetryConfiguratorTest extends TestCase
{
    public function testConfiguratorDefaultConfig()
    {
        $retryConfigurator = new RetryConfigurator();

        $this->assertSame(2, $retryConfigurator->getMaxRetries());
        $this->assertSame(300, $retryConfigurator->getDelayInMs());
        $this->assertSame([\Throwable::class], $retryConfigurator->getRetryableExceptions());
    }

    public function testConfiguratorConstructor(): void
    {
        $retryConfigurator = new RetryConfigurator(1, 100, \Exception::class);

        $this->assertSame(1, $retryConfigurator->getMaxRetries());
        $this->assertSame(100, $retryConfigurator->getDelayInMs());
        $this->assertSame([\Exception::class], $retryConfigurator->getRetryableExceptions());
    }

    public function testConfiguratorSetters(): void
    {
        $retryConfigurator = new RetryConfigurator();
        $retryConfigurator
            ->setMaxRetries(1)
            ->setDelayInMs(100)
            ->setRetryableExceptions(\Exception::class, \Error::class)
        ;

        $this->assertSame(1, $retryConfigurator->getMaxRetries());
        $this->assertSame(100, $retryConfigurator->getDelayInMs());
        $this->assertSame([\Exception::class, \Error::class], $retryConfigurator->getRetryableExceptions());
    }
}
