
[![Build Status](https://travis-ci.org/php-vivace/di.svg?branch=master)](https://travis-ci.org/php-vivace/di)
[![Code Climate](https://codeclimate.com/github/php-vivace/di/badges/gpa.svg)](https://codeclimate.com/github/php-vivace/di)
[![Test Coverage](https://codeclimate.com/github/php-vivace/di/badges/coverage.svg)](https://codeclimate.com/github/php-vivace/di/coverage)
[![Issue Count](https://codeclimate.com/github/php-vivace/di/badges/issue_count.svg)](https://codeclimate.com/github/php-vivace/di)
[![Latest Stable Version](https://poser.pugx.org/vivace/di/v/stable)](https://packagist.org/packages/vivace/di)
[![composer.lock](https://poser.pugx.org/vivace/di/composerlock)](https://packagist.org/packages/vivace/di)
[![Monthly Downloads](https://poser.pugx.org/vivace/di/d/monthly)](https://packagist.org/packages/vivace/di)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/php-vivace/di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/php-vivace/di/?branch=master)

## Synopsis
Inversion of Control container with support for advanced inheritance of the container.

## Code Example

vendor/name/PackageA.php
```php
class PackageA extends \vivace\di\Package
{
    public function __construct()
    {
        $this->export('vendorA\tool\ClassA', function (\vivace\di\Scope $scope) {
            $logger = $scope->import(Psr\Log\LoggerInterface::class);
            $db = $scope->import(\PDO::class);
            $cache = $scope->import(\Psr\Cache\CacheItemPoolInterface::class);
            return new vendorA\tool\ClassA($db, $cache, $logger);
        });

        $this->export('vendorA\tool\ClassB', function (\vivace\di\Scope $scope) {
            $db = $scope->import(\PDO::class);
            $cache = $scope->import(\Psr\Cache\CacheItemPoolInterface::class);
            return new vendorA\tool\ClassB($db, $cache);
        });
    }
}
```

libs/RedisCache.php
```php
class RedisCache extends \vivace\di\Package
{
    public function __construct(string $prefix = null)
    {
        $this->export(\Psr\Cache\CacheItemPoolInterface::class, function (\vivace\di\Scope $scope) use ($prefix) {
            static $instance;
            if ($instance) {
                return $instance;
            }
            $logger = $scope->import(Psr\Log\LoggerInterface::class);
            $instance = new RedisCache();
            $instance->setPrefix($prefix);
            $instance->setLogger($logger);
            return $instance;
        });
    }
}
```

app/Main.php
```php
class Main extends \vivace\di\Package
{
    public function __construct()
    {
        $this->export('cache.dummy', function () {
            return new DummyCache();
        });
        //By default, all logs are written to the file
        $this->export(\Psr\Log\LoggerInterface::class, function () {
            return new FileLogger();
        });
        //Default db connection
        $this->export(PDO::class, function () {
            return new PDO('<main_db_dsn>');
        });
        //Additional db connection
        $this->export('db.second', function () {
            return new PDO('<main_db_dsn>');
        });

        $this->use(new RedisCache('app_prefix'))
            ->as(\Psr\Cache\CacheItemPoolInterface::class, 'cache.redis');

        $this->use(new PackageA())
            //Disable the cache through the use of "DummyCache" for "vendor\tool\ClassB"
            ->insteadFor('vendorA\tool\ClassB', \vivace\di\Package::new([
                \Psr\Cache\CacheItemPoolInterface::class => 'cache.dummy'
            ]))
            ->insteadOf(\Psr\Cache\CacheItemPoolInterface::class, 'cache.redis')
            ->insteadOf(PDO::class, 'db.second');
    }
}

```

web/index.php

```php
$scope = new Main();
$instanceB = $scope->import(vendorA\tool\ClassB::class);
$instanceA = $scope->import(vendorA\tool\ClassA::class);
$cache = $scope->import(\Psr\Cache\CacheItemPoolInterface::class);
```
## Motivation

It took the opportunity to create a portable and extensible Inversion of Control containers

## Installation
```bash
composer require vivace/di
```

## API Reference

...

## Tests

...

## Contributors

...

## License

...