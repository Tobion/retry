Retry
=====

PHP library for retrying code, e.g. HTTP requests or database transactions, in case of failures.

[![Build Status](https://travis-ci.org/Tobion/retry.svg)](https://travis-ci.org/Tobion/retry)

Installation
------------

    $ composer require tobion/retry

Usage
-----

```php
use Tobion\Retry\Retry;

$callableThatMightFail = function (int $arg1, int $arg2): int {
    if (random_int(1, 2) % 2) {
        throw new \RuntimeException('Sudden error');
    }

    return $arg1 + $arg2;
};

// Allows you to call the callable with parameters and retry its execution in case an exception is thrown.
// You can access the return value of the callable (3 in this case).
$returnValue = Retry::call($callableThatMightFail, 1, 2);

// By default:
// - The callable is retried twice (i.e. max three executions). If it still fails, the last error is rethrown.
// - Retries have a 300 milliseconds delay between them.
// - Every \Throwable will trigger the retry logic, i.e. both \Exception and \Error.
// You can adjust the retry logic like this:
$retryingCallable = Retry::configure()
    ->setMaxRetries(5)
    ->setDelayInMs(100)
    ->setRetryableExceptions(\RuntimeException::class) // other failures like \TypeError will not be retried
    ->decorate($callableThatMightFail)
;
$returnValue = $retryingCallable(1, 2);
// $retryingCallable just decorates the original callable and can be used like it.
// To find out how often it had to retry, you can use:
$retryingCallable->getRetries();
```

Contributing
------------

To run tests:

    $ composer install
    $ vendor/bin/phpunit
