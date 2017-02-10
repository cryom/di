
[![Build Status](https://travis-ci.org/php-vivace/di.svg?branch=master)](https://travis-ci.org/php-vivace/di)
[![Code Climate](https://codeclimate.com/github/php-vivace/di/badges/gpa.svg)](https://codeclimate.com/github/php-vivace/di)
[![Test Coverage](https://codeclimate.com/github/php-vivace/di/badges/coverage.svg)](https://codeclimate.com/github/php-vivace/di/coverage)
[![Issue Count](https://codeclimate.com/github/php-vivace/di/badges/issue_count.svg)](https://codeclimate.com/github/php-vivace/di)
[![Latest Stable Version](https://poser.pugx.org/vivace/di/v/stable)](https://packagist.org/packages/vivace/di)
[![composer.lock](https://poser.pugx.org/vivace/di/composerlock)](https://packagist.org/packages/vivace/di)
[![Monthly Downloads](https://poser.pugx.org/vivace/di/d/monthly)](https://packagist.org/packages/vivace/di)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/php-vivace/di/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/php-vivace/di/?branch=master)

## About
__vivace\di__ -  a container of Inversion of Control. The basis is the possibility of inheritance of containers(further _Scope_) with the ability to resolve the collision dependency, almost the same as it is implemented in the __traits__. Support __autowired__ (through reflection api with an opportunity caching)

You can create multiple _scopes_, which will eventually be combined into the main area of action. Look at the following example:

ControlPanel.php -  _Scope_ for resolving dependency for control panel module.
```php
class ControlPanel extends \vivace\di\Scope
{
    public function __construct()
    {
        //export in the "Scope" logging component to the factory can get it
        $this->export(\psr\Log\LoggerInterface::class, function (\vivace\di\type\Scope $scope) {
            return MyDbLogger($scope->import(PDO::class));
        });
    }
}
```
```php
$scope = new ControlPanel();
$scope->import(\psr\Log\LoggerInterface::class);
//An exception is thrown, due to the fact that in the "Scope" is not exported object PDO class.
//Ok, see further
```

Blog.php - _Scope_ for resolving dependency for blog module.
```php
class Blog extends \vivace\di\Scope
{
    public function __construct()
    {
        $this->export(\psr\Log\LoggerInterface::class, function (\vivace\di\type\Scope $scope) {
            return BlogDbLogger($scope->import(PDO::class));
        });
    }
}
```
Main.php -  
```php
class Main extends \vivace\di\Scope
{
    /** @var array */
    private $pdoInstances = [];

    /**
     * Main constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->export('db_control_panel', function () use ($config) {
            return $this->producePdo('pgsql://dsn_db_control_panel');
        });

        $this->export('db_blog', function () {
            return $this->producePdo('pgsql://dsn_db_blog');
        });

        $this->inherit(new ControlPanel())
            ->insteadOf(PDO::class, 'db_control_panel')
            ->as(\psr\Log\LoggerInterface::class, 'control_panel_logger');

        $this->inherit(new Blog())
            ->insteadOf(PDO::class, 'db_blog')
            ->as(\psr\Log\LoggerInterface::class, 'blog_logger');
    }

    /**
     * @param string $dsn
     * @return mixed|PDO
     */
    protected function producePdo(string $dsn)
    {
        return $this->pdoInstances[$dsn] ?? $this->pdoInstances[$dsn] = new PDO($dsn);
    }
}
```
index.php
```php
$main = new Main(['config' => 'value']);

$main->import('blog_logger')->emergency('Message for blog logger');
$main->import('control_panel_logger')->emergency('Message for control panel');
```
