<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 02.03.17
 * Time: 23:46
 */

namespace vivace\di\tests;


use PHPUnit\Framework\TestCase;
use vivace\di\BadDefinitionError;
use vivace\di\Package;
use vivace\di\Container\Base;
use vivace\di\Scope;

class PackageTest extends TestCase
{
    public function testU()
    {
        $pkg = new class
        {
            use Package;

            public function __construct()
            {
                $this->export('foo', function (Scope $scope) {
                    return $scope->import('foo');
                });
                $this->export('bar', function (Scope $scope) {
                    return $scope->import('bar');
                });
                $this->use(new Base([
                    'foo' => function () {
                        return 'foo1';
                    },
                    'bar' => function () {
                        return 'bar1';
                    },
                    'boo' => function () {
                        return 'boo';
                    },
                ]));
            }
        };
        $this->assertEquals('foo1', $pkg->getScope()->import('foo'));
        $this->assertEquals('bar1', $pkg->getScope()->import('bar'));
        $this->assertEquals('boo', $pkg->getScope()->import('boo'));
    }

    public function testBadDefinition()
    {
        $this->expectException(BadDefinitionError::class);
        new class
        {
            use Package;

            public function __construct()
            {
                $this->export('foo', function () {
                    return 'foo';
                });
                $this->export('foo', function () {
                    return 'foo';
                });
            }
        };
    }

    public function testScope()
    {
        $pkg = new class
        {
            use Package;

            public function __construct()
            {
                $this->export('foo', function () {
                    return 'foo';
                });
            }
        };

        $this->assertEquals('foo', $pkg->getScope()->import('foo'));
    }
}