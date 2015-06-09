Retry
=====

PHP class for retrying code pieces in case of exceptions.

[![Build Status](https://travis-ci.org/Tobion/retry.svg)](https://travis-ci.org/Tobion/retry)

Born out of a need of [Tobias Schultze](https://github.com/Tobion) and [Christian Riesen](https://github.com/ChristianRiesen) for a project at [Liip](http://www.liip.ch)
which then found a specific [merge request into Doctrine](https://github.com/doctrine/dbal/pull/718/files). We
thought this works just as well on a generic basis.

Usage
-----

Make sure it's autoloaded. Wrap the code you want retried with the class and execute it.

```php
use Tobion\Retry\Retry;

// Anon function
$retry = new Retry(function () { return 42; });

// Outputs 42
echo $retry();

```

You can configure the exceptions to listen to for retries, the number of retries and the time between retries (in milliseconds) at construction time of the Retry class.

By default it will catch all exceptions

Development
-----------

To run tests, you need [Composer](https://getcomposer.org/) and then execute:

     $ composer install
     $ vendor/bin/phpunit
