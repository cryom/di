
[![Build Status](https://travis-ci.org/php-vivace/di.svg?branch=master)](https://travis-ci.org/php-vivace/di)
[![Code Climate](https://codeclimate.com/github/php-vivace/di/badges/gpa.svg)](https://codeclimate.com/github/php-vivace/di)
[![Test Coverage](https://codeclimate.com/github/php-vivace/di/badges/coverage.svg)](https://codeclimate.com/github/php-vivace/di/coverage)
[![Issue Count](https://codeclimate.com/github/php-vivace/di/badges/issue_count.svg)](https://codeclimate.com/github/php-vivace/di)
[![Latest Stable Version](https://poser.pugx.org/vivace/di/v/stable)](https://packagist.org/packages/vivace/di)
[![composer.lock](https://poser.pugx.org/vivace/di/composerlock)](https://packagist.org/packages/vivace/di)
[![Monthly Downloads](https://poser.pugx.org/vivace/di/d/monthly)](https://packagist.org/packages/vivace/di)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/php-vivace/di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/php-vivace/di/?branch=master)

## Synopsis
Inversion of Control container with support for advanced inheritance and resolving dependencies of the container.
Use to build unrelated modular systems.
See example for understanding.

## Code Example

### Base
./vendor/blog/Package.php - Scope for use 
```php
namespace blog;

class Package extends \vivace\di\Scope\Package
{
    public function __construct()
    {
        //define a new factory, which instantiate \blog\Widget with resolving of dependenties and return it
        $this->export('blog\Widget', function (\vivace\di\Scope $scope) {
            
            //import instance of \PDO from main scope
            $db = $scope->import(\PDO::class);
            
            //import instance of \PDO from main scope
            $cache = $scope->import(\Psr\Cache\CacheItemPoolInterface::class);
            
            //import instance of Psr\Log\LoggerInterface from main scope
            $logger = $scope->import(Psr\Log\LoggerInterface::class);
            
            return new \blog\Widget($db, $cache, $logger);
        });
        
        
        //Below are defined, through call of "export" method, all the components necessary for working blog.
        //...
    }
    
}
```

./vendor/admin/Package.php - from a third party developer
```php
namespace admin;

class Package extends \vivace\di\Scope\Package
{   
    public function __construct(string $mode)
    {
        // export logger which will be use in this scope, if on top wont exported another instance of Logger
        $this->export(Psr\Log\LoggerInterface::class, function (\vivace\di\Scope $scope) {
            return new admin\Logger();
        })
            
        $this->export('admin\Widget', function (\vivace\di\Scope $scope) use ($mode) {
            //Default will be use Psr\Log\LoggerInterface::class of this scope
            $logger = $scope->import(Psr\Log\LoggerInterface::class);
            $db = $scope->import(\PDO::class);
            $widget = new \admin\Widget($db);
            $widget->setLogger($logger);
            $widget->setMode($mode);
            
            return $widget;
        });
        
    }
}
```

./src/Package.php - your application package
```php
namespace app;
class Package extends \vivace\di\Scope\Package
{
    public function __construct()
    {
        //Below is a lot of code, of course you can make it more convenient for perception 
        
        //By default, all logs are written to the file
        $this->export(\Psr\Log\LoggerInterface::class, function () {
            return new FileLogger();
        });
        
        //define dummy logger for disable logs for some packages
        $this->export('logger.dummy', function () {
            return new DummyLogger();
        });
        
        //Export connection to main data base.
        $this->export(PDO::class, function () {
            return new PDO('<main_db_dsn>');
        });
        //Export additional connection for other data base.
        $this->export('db.second', function () {
            return new PDO('<second_db_dsn>');
        });
        //This method allow this package inherit other packages
        $this->use(new \blog\Package())
            //this method allow resolve dependencies for concrete components exported inside package.
            ->insteadFor('blog\Widget', [
                \Psr\Cache\CacheItemPoolInterface::class => function(vivace\di\Scope $scope){
                    return MyCacheComponent();    
                }
            ])
            //This method allows you to replace one dependent component with another.
            //Inside the package (in this case \blog\Package) when importing the \PDO instance, will return an instance of 'db.second'
            ->insteadOf(PDO::class, 'db.second');
            
        $this->use(new \admin\Package())
            // Disable logs for \admin\Package through use dummy implementation of Psr\Log\LoggerInterface, 
            ->insteadOf(Psr\Log\LoggerInterface::class, 'logger.dummy')
            //This method allow set alias for component for resolve of name conflict, now admin\Widget can be imported by class name and by alias 'admin' 
            ->as('admin\Widget', 'admin')
    }
}

```

web/index.php

```php
$scope = new app\Package();
$blog = $scope->import(blog\Widget::class);
var_dump($blog instanceof \blog\Widget::class);// true

$admin = $scope->import('admin');
var_dump($admin instanceof \admin\Widget::class);// true

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
