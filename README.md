# di
[![Build Status](https://travis-ci.org/php-vivace/di.svg?branch=master)](https://travis-ci.org/php-vivace/di)
[![Code Climate](https://codeclimate.com/github/php-vivace/di/badges/gpa.svg)](https://codeclimate.com/github/php-vivace/di)
[![Test Coverage](https://codeclimate.com/github/php-vivace/di/badges/coverage.svg)](https://codeclimate.com/github/php-vivace/di/coverage)
[![Issue Count](https://codeclimate.com/github/php-vivace/di/badges/issue_count.svg)](https://codeclimate.com/github/php-vivace/di)

#### Example:

```php
class Models extends vivace\di\Scope{
   $this->export('\PDO', function(){
      return new \PDO('default_pdo_connection');
   });
   $this->export('models\User', function(vivace\di\type Scope $scope){
      return new models\User($scope->import('\PDO'));
   });
   $this->export('models\Auth', function(vivace\di\type Scope $scope){
      return new models\Auth($scope->import('\PDO'));
   });
}

class Main extends vivace\di\Scope {
  private $instances = [];
  public function __construct(){
    $this->export('db_main', function(){
      return $this->newDbConnection('<dsn_for_db_main>');
    });
    $this->export('db_auth', function(){
      return $this->newDbConnection('<dsn_for_db_auth>');
    })
    $this->inherit(new Models())
        ->bind('Model\Auth', ['\PDO' => 'db_auth'])
        ->as('Model\Auth', 'Common\Auth')
        ->insteadOf('\PDO', 'db_main');
    
  }
  
  public function newDbConnection(string $dsn){
    return $this->instances['PDO'] ?? $this->instances['PDO'] = new \PDO($dsn);
  }
}

////////index.php


$scope = new Main();

$auth = $scope->import('Common\Auth'); // equal to $scope->import('models\Auth')
$pdo = $scope->import('\PDO');// equal to $scope->import('db_main')
$user = $scope->import('models\User');
```
