<?php
namespace vivace\di\tests\Factory;

use PHPUnit\Framework\TestCase;
use vivace\di\Factory\Persistent;
use vivace\di\Resolver;
use vivace\di\Scope;
use vivace\di\Scope\Branch;
use vivace\di\tests\fixture\Emp;

class PersistentTest extends TestCase
{
    public function setUp()
    {
        require_once __DIR__ . '/../fixture/classes.php';
    }

    public function testPersistent()
    {
        $f = new Persistent(Emp::class);
        $scope = new Branch([
            Resolver::class => function (Scope $scope) {
                return new Resolver($scope);
            },
        ]);
        $this->assertSame($f($scope), $f($scope));
    }
}