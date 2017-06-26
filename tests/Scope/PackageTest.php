<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 04.03.17
 * Time: 14:06
 */

namespace vivace\di\tests\Scope;


use PHPUnit\Framework\TestCase;
use vivace\di\Container\Base;
use vivace\di\Factory;
use vivace\di\Scope;
use vivace\di\Scope\Package;
use vivace\di\tests\fixture\Baz;
use vivace\di\tests\fixture\BazInterface;
use vivace\di\tests\fixture\Foo;

class PackageTest extends TestCase
{
    public function setUp()
    {
        require_once dirname(__DIR__) . '/fixture/classes.php';
    }

    public function testHas()
    {
        $pkg = new class extends Package
        {
            public function __construct()
            {
                $this->export('a', \vivace\di\wrap('a'));
                $this->export('b', \vivace\di\wrap('b'));
                $this->use(new Base([
                    'd' => function () {
                        return 'd';
                    },
                ]));
            }
        };
        $this->assertTrue($pkg->has('a'));
        $this->assertTrue($pkg->has('b'));
        $this->assertTrue($pkg->has('d'));
        $this->assertFalse($pkg->has('c'));
    }

    public function testGet()
    {
        $pkg = new class extends Package
        {
            public function __construct()
            {
                $this->export('a', \vivace\di\wrap('a'));
                $this->export('b', \vivace\di\wrap('b'));
                $this->use(new Base([
                    'd' => function (Scope $scope) {
                        return 'd' . $scope->import('a');
                    },
                ]));
            }
        };
        $this->assertEquals('a', call_user_func($pkg->get('a'), $pkg));
        $this->assertEquals('b', call_user_func($pkg->get('b'), $pkg));
        $this->assertEquals('da', call_user_func($pkg->get('d'), $pkg));
    }

    public function testImport()
    {
        $pkg = new class extends Package
        {
            public function __construct()
            {
                $this->export('a', \vivace\di\wrap('a'));
                $this->export('b', \vivace\di\wrap('b'));
                $this->use(new Base([
                    'd' => function (Scope $scope) {
                        return 'd' . $scope->import('a');
                    },
                ]));
            }
        };

        $this->assertEquals('a', $pkg->import('a'));
        $this->assertEquals('b', $pkg->import('b'));
        $this->assertEquals('da', $pkg->import('d'));
        $this->expectException(\Throwable::class);
        $pkg->import('ddd');
    }

    public function testClass()
    {
        $pkg = new class extends Package
        {
            public function __construct()
            {
                $this->class(Foo::class, ['val' => 1]);
            }
        };

        $this->assertInstanceOf(Foo::class, $foo = $pkg->import(Foo::class));
        $this->assertEquals(1, $foo->val);
    }

    public function testInterface()
    {
        $pkg = new class extends Package
        {
            public function __construct()
            {
                $this->interface(BazInterface::class, Baz::class);
            }
        };

        $this->assertInstanceOf(BazInterface::class, $pkg->import(BazInterface::class));
        $this->assertInstanceOf(Baz::class, $pkg->import(BazInterface::class));
    }

    public function testAutowire()
    {
        $pkg = new class extends Package
        {
        };

        $this->assertInstanceOf(Baz::class, $pkg->import(Baz::class));
    }

    public function testGetFactoryForNotDefinedObject()
    {
        $pkg = new class extends Package
        {
        };
        $this->assertInternalType('callable', $pkg->get(Baz::class));
    }

    public function testImportByAliasWithAutowire()
    {
        $pkg = new class extends Package
        {
            public function __construct()
            {
                $this->as(Baz::class, BazInterface::class);
            }
        };

        $this->assertInstanceOf(BazInterface::class, $pkg->import(BazInterface::class));
    }
}