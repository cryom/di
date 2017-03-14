
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
Than it is similar to "bundles" from the framework ___symfony___.


## Code Example

### Base
vendor/name/PackageA.php - from a third party developer
```php
class PackageA extends vivace\di\Scope\Package
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

libs/RedisCache.php - from a third party developer
```php
class RedisCache extends BaseCache
{
    use \vivace\di\Package;
    
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

app/Main.php - your application package
```php
class Main extends \vivace\di\Scope\Package
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
        /*
        'RedisCache' not implement 'ContainerInterface', only used trait of 'vivace\di\Package', which has method 'getScope'. 
        Result of method 'getScope' is object instantiated of 'ContainerInterface', which can be passed in 'use' method
        */
        $this->use((new RedisCache('app_prefix'))->getScope())
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
### Use Factory\Instance
```php
class Session {
    public function __construct($prefix){
    }
}
```
```php
class Orm {
    private $pdo;
    private $session;
    
    public function __construct(PDO $pdo, Session $session){
        $this->pdo = $pdo;
        $this->session = $session;
    }
    
    public function getPdo(): \PDO {
        return $this->pdo;
    }
    
    public function getSession(): Session {
        return $this->session;
    }
}
```
```php
class Package extends vivace\di\Scope\Package {
    public function __construct(){
        //Factory\Instance require object of Resolver for dependencies values resolving 
        $this->export(vivace\di\Resolver::class, vivace\di\Resolver::getFactory());
        
        $this->export(\PDO::class, $this->getPDOFactory());
        
        $this->export(Session::class, new Factory(Session::class, ['prefix' => 'your_session_prefix'], true));
        
        $this->export(Orm::class, new Factory(Orm::class));
    }
    
    
    private function getPDOFactory(){
        $factory = new vivace\di\Factory\Instance(PDO::class);
        //parameters for constructor
        $factory->setParameters(['dsn' => '<your_connection_dsn>']);
        //singletone
        $factory->asService();
        //after create call follow function
        $factory->setUp(function(\PDO $pdo, vivace\di\Scope $scope){
                                    $pdo->exec("set names utf8");
                                 });
                                 
        return $factory;
    }
}
```

index.php
```php
$pkg = new Package();
$orm = $pkg->import(Orm::class);
var_dump($orm instanceof Orm);//true
var_dump($orm->getPDO() === $pkg->import(Orm::class)->getPDO()); //true
var_dump($orm->getSession() instanceof Session); //true
```
### Auto-resolution of constructor parameters (Autowiring)
model/User.php
```php
namespace model;

class User {
    public $pdo;
    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }
}
```

app/Package.php
```php
namespace app;

class Package extends vivace\di\Scope\Package {
    public function __construct(){
        $autowire = new Autowire();
        // Autowire allows configure strategy of factory for each class
        $autowire->get(\PDO::class)
                 ->asService()
                 ->setParameters(['dsn' => 'psql://yourdsn'])
                 ->setUp(function(\PDO $pdo, vivace\di\Scope $scope){
                    $pdo->exec("set names utf8");
                 });
                 
        $this->use(new Autowire())
    }
} 
```
web/index.php
```php
    require dirname(__DIR__) . '/vendor/autoload.php';
    $package = new app\Package();
    // We don't export factory for "model\User", but we can import instance of this class, 
    // because used Autowire, which create factory independently, when we be import.
    $user = $package->import(model\User::class);
    
    var_dump($user->pdo === $package->import(\PDO::class));//true
    var_dump($user === $package->import(model\User::class));//false
```

## Motivation

The main goal is to create a portable containers for modular application.

## Installation
```bash
composer require vivace/di
```

## API Reference

...

## Tests

via docker-compose

docker-compose run --rm phpunit --testsuite=unit

via php

 phpunit --testsuite=unit

## Contributors

...

## License

Copyright (c) 2017 Albert Sultanov

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
