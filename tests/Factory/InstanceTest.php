<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 27.02.17
 * Time: 3:03
 */

namespace vivace\di\tests\Factory;


use PHPUnit\Framework\TestCase;
use vivace\di\BadDefinitionError;
use vivace\di\Factory\Instance;
use vivace\di\ImportFailureError;
use vivace\di\Resolver;
use vivace\di\Scope\Branch;
use vivace\di\tests\fixture\Bar;
use vivace\di\tests\fixture\Foo;

class InstanceTest extends TestCase
{
    public function setUp()
    {
        require_once __DIR__ . '/../fixture/classes.php';
    }

    public function testFactoryConstruct()
    {
        $this->expectException(BadDefinitionError::class);
        new Instance('123');
    }

    public function testFactoryArguments()
    {
        $scope = new Branch([Resolver::class => Resolver::getFactory()]);
        $factory = new Instance(Foo::class);
        $foo = $factory->setParameters(['val' => 'value'])->produce($scope);
        $this->assertEquals('value', $foo->val);
    }


    public function testFactoryService()
    {
        $scope = new Branch([Resolver::class => Resolver::getFactory()]);
        $factory = new Instance(Foo::class);
        $this->assertNotSame($factory->produce($scope), $factory->produce($scope));
        $factory->asService();
        $this->assertSame($factory->produce($scope), $factory->produce($scope));
    }


    public function testFactoryApply()
    {
        $scope = new Branch([Resolver::class => Resolver::getFactory()]);
        $factory = new Instance(Foo::class);
        $factory->setParameters(['val' => 123]);
        $factory->setUp(function (Foo $foo) {
            $foo->val = 'value';
        });
        $foo = $factory->produce($scope);
        $this->assertEquals('value', $foo->val);
    }

    public function testNotResolved()
    {
        $this->expectException(ImportFailureError::class);
        $factory = new Instance(Bar::class);
        $factory->produce(new Branch([Resolver::class => Resolver::getFactory()]));
    }
}