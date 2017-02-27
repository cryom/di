<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 27.02.17
 * Time: 1:47
 */

namespace vivace\di\tests;


use PHPUnit\Framework\TestCase;
use vivace\di\Autowire;
use vivace\di\ContainerProxy;
use vivace\di\Package;
use vivace\di\Scope;
use vivace\di\tests\fixture\Bar;
use vivace\di\tests\fixture\Foo;

class AutowireTest extends TestCase
{
    public function setUp()
    {
        require_once __DIR__ . '/fixture/classes.php';
    }

    public function testHas()
    {
        $autowire = new Autowire();
        $this->assertTrue($autowire->has(Bar::class));
        $this->assertFalse($autowire->has('123not_class_name'));
    }

    public function testImport()
    {
        $proxy = new ContainerProxy(new Autowire());
        $package = Package::new([], $proxy);
        $this->assertInstanceOf(Bar::class, $package->import(Bar::class));
    }

    public function testFactoryCustomize()
    {
        $autowire = new Autowire();
        $this->assertNotSame($autowire->get(Bar::class), $autowire->get(Bar::class));

        $autowire = new Autowire();
        $autowire->get(Bar::class)->asService();
        $this->assertSame($autowire->get(Bar::class), $autowire->get(Bar::class));

        $autowire = new Autowire();
        $autowire->get(Bar::class)->setArguments([]);
        $this->assertSame($autowire->get(Bar::class), $autowire->get(Bar::class));

        $autowire = new Autowire();
        $autowire->get(Bar::class)->setUp(function () {

        });
        $this->assertSame($autowire->get(Bar::class), $autowire->get(Bar::class));
    }

    public function testRedefineImport()
    {
        $autowire = new ContainerProxy(new Autowire());
        $children = new ContainerProxy(Package::new([
            Foo::class => function (Scope $scope) {
                return new Foo('abc');
            },
        ]));
        $package = Package::new([], $autowire, $children);
        $this->assertEquals('abc',$package->import(Foo::class)->val);
    }
}