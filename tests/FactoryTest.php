<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 27.02.17
 * Time: 3:03
 */

namespace vivace\di\tests;


use PHPUnit\Framework\TestCase;
use vivace\di\BadDefinitionError;
use vivace\di\Factory;
use vivace\di\ImportFailureError;
use vivace\di\Package;
use vivace\di\Resolver;
use vivace\di\tests\fixture\Bar;
use vivace\di\tests\fixture\Foo;

class FactoryTest extends TestCase
{
    public function setUp()
    {
        require_once __DIR__ . '/fixture/classes.php';
    }

    public function testFactoryConstruct()
    {
        $this->expectException(BadDefinitionError::class);
        new Factory('123');
    }

    public function testFactoryArguments()
    {
        $scope = Package::new([Resolver::class => Resolver::getFactory()]);
        $factory = new Factory(Foo::class);
        $foo = $factory->setArguments(['val' => 'value'])->produce($scope);
        $this->assertEquals('value', $foo->val);
    }


    public function testFactoryService()
    {
        $scope = Package::new([Resolver::class => Resolver::getFactory()]);
        $factory = new Factory(Foo::class);
        $this->assertNotSame($factory->produce($scope), $factory->produce($scope));
        $factory->asService();
        $this->assertSame($factory->produce($scope), $factory->produce($scope));
    }


    public function testFactoryApply()
    {
        $scope = Package::new([Resolver::class => Resolver::getFactory()]);
        $factory = new Factory(Foo::class);
        $factory->setArguments(['val' => 123]);
        $factory->setUp(function (Foo $foo) {
            $foo->val = 'value';
        });
        $foo = $factory->produce($scope);
        $this->assertEquals('value', $foo->val);
    }

    public function testNotResolved()
    {
        $this->expectException(ImportFailureError::class);
        $factory = new Factory(Bar::class);
        $factory->produce(Package::new([Resolver::class => Resolver::getFactory()]));
    }
}