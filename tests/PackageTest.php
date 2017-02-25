<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 23.02.17
 * Time: 1:25
 */

namespace vivace\di\tests;


use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use vivace\di\Container;
use vivace\di\ContainerProxy;
use vivace\di\exception\ImportFailure;
use vivace\di\Package;
use vivace\di\Scope;

class PackageTest extends TestCase
{
    private function makePackage(array $factories, ...$used): Package
    {
        return new class($factories, $used) extends Package
        {
            public function __construct(array $factories, array $used)
            {
                foreach ($factories as $id => $factory) {
                    if (!is_callable($factory)) {
                        $factory = function () use ($factory) {
                            return $factory;
                        };
                    }
                    $this->export($id, $factory);
                }

                foreach ($used as $container) {
                    if (is_array($container)) {
                        $calls = $container;
                        $container = array_shift($calls);
                        $resolution = $this->use($container);
                        foreach ($calls as $name => $arguments) {
                            foreach ($arguments as $argument) {
                                $resolution = $resolution->{$name}(...$argument);
                            }
                        }
                    } else {
                        $this->use($container);
                    }
                }
            }
        };
    }

    public function testHas()
    {
        $package = $this->makePackage(['foo' => 'foo'], $this->makePackage(['ddd' => 'ddd']));

        $this->assertTrue($package->has('foo'));
        $this->assertFalse($package->has('bar'));
    }

    public function testGetException()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $package = $this->makePackage(['foo' => 'foo']);
        $package->get('undefined');
        $package = $this->makePackage(['foo' => 'foo'], $this->makePackage(['ddd' => 'ddd']));
        $package->get('undefined');
    }

    public function testImportException()
    {
        $this->expectException(ImportFailure::class);
        $package = $this->makePackage(['foo' => 'foo']);
        $package->import('undefined');
        $package = $this->makePackage(['foo' => 'foo'], $this->makePackage(['ddd' => 'ddd']));
        $package->import('undefined');
    }

    public function testGet()
    {
        $package = $this->makePackage([
            'foo' => $foo = function () {
            },
            'bar' => $bar = function () {
            },
        ]);

        $this->assertEquals($foo, $package->get('foo'));
        $this->assertEquals($bar, $package->get('bar'));
    }

    /**
     * @depends testGet
     * @depends testHas
     */
    public function testImport()
    {
        $package = $this->makePackage([
            'foo' => 'foo',
            'bar' => 'bar',
        ]);

        $this->assertEquals('foo', $package->import('foo'));
        $this->assertEquals('bar', $package->import('bar'));
    }

    public function testHasThroughUse()
    {
        $package = $this->makePackage(
            ['foo' => 'foo'],
            $this->makePackage(['bar' => 'bar'])
        );

        $this->assertTrue($package->has('bar'));
    }

    public function testGetThroughUse()
    {
        $package = $this->makePackage(
            ['foo' => 'foo'],
            $this->makePackage([
                'bar' => $barFactory = function () {
                    return 'bar';
                },
            ])
        );
        $this->assertEquals($barFactory, $package->get('bar'));
    }

    public function testImportThroughUse()
    {
        $this->expectException(ImportFailure::class);

        $package = $this->makePackage([
            'bar' => 'bar',
        ], $this->makePackage([
            'foo' => function (Scope $scope) {
                return 'foo' . $scope->import('bar');
            },
        ], $this->makePackage([
            'baz' => function (Scope $scope) {
                return 'baz' . $scope->import('foo');
            },
        ]))
        );

        $this->assertEquals('bazfoobar', $package->import('baz'));
        $package->import('undefined');
    }

    public function testImportWithRecursion()
    {
        $package = $this->makePackage(
            [
                'foo' => function (Scope $scope) {
                    return 'foo1' . $scope->import('foo');
                },
            ],
            $this->makePackage(
                [
                    'foo' => 'foo2',
                ],
                $this->makePackage(
                    [
                        'bar' => function (Scope $scope) {
                            return 'bar' . $scope->import('foo');
                        },
                    ]
                )
            )
        );

        $this->assertEquals('foo1foo2', $package->import('foo'));
        $this->assertEquals('barfoo1foo2', $package->import('bar'));
    }

    public function testImportByAlias()
    {
        $package = $this->makePackage(
            [
                'foo' => function (Scope $scope) {
                    return 'foo1' . $scope->import('buz') . $scope->import('ddd');
                },
                'tas' => function () {
                    return 'tas1';
                },
            ],
            [
                $this->makePackage(
                    [
                        'foo' => function (Scope $scope) {
                            return 'foo2';
                        },
                        'tas' => function () {
                            return 'tas2';
                        },
                    ]
                ),
                'as' => [['foo', 'buz'], ['tas', 'ddd']],
            ]
        );

        $this->assertEquals('foo1foo2tas2', $package->import('foo'));
    }

    public function testImportWithInsteadOf()
    {
        $this->expectException(ImportFailure::class);
        $package = $this->makePackage(
            [
                'foo' => 'foo',
                'baz' => 'baz',
            ],
            [
                $this->makePackage(
                    [
                        'bar' => function (Scope $scope) {
                            return 'bar' . $scope->import('foo');
                        },
                        'fan' => function (Scope $scope) {
                            return $scope->import('undefined');
                        },
                    ]
                ),
                'insteadOf' => [
                    ['foo', 'baz'],
                ],
            ]
        );

        $this->assertEquals('barbaz', $package->import('bar'));
        $package->import('fan');
    }

    public function testImportWithInsteadFor()
    {
        $container = Container::new([
            'c' => function () {
                return 'c';
            },
            'foo' => function (Scope $scope) {
                return $scope->import('a') . $scope->import('b') . $scope->import('c');
            },
            'bar' => function (Scope $scope) {
                return $scope->import('a') . $scope->import('b') . $scope->import('c');
            },
        ]);
        $use = new ContainerProxy($container);
        $use->insteadFor('foo', [
            'a' => 'a1',
        ]);
        $package = Package::new([
            'a' => function () {
                return 'a';
            },
            'b' => function () {
                return 'b';
            },
            'a1' => function () {
                return 'a1';
            },
        ], $use);

        $this->assertEquals('a1bc', $package->import('foo'));
        $this->assertEquals('abc', $package->import('bar'));
    }

    public function testImportWithFinal()
    {
        $container = Container::new([
            'foo' => function (Scope $scope) {
                return 'foo' . $scope->import('bar');
            },
            'bar' => function () {
                return 'bar';
            },
        ]);
        $proxy = new ContainerProxy($container);
        $proxy->final('bar');
        $container2 = Container::new([
            'bar' => function (Scope $scope) {
                return 'bar2';
            },
        ]);
        $proxy2 = new ContainerProxy($container2);
        $proxy2->as('bar', 'bar2');
        $scope = Package::new([
            'bar' => function () {
                return 'bar_override';
            },
        ], $proxy2, $proxy);

        $this->assertEquals('foobar', $scope->import('foo'));
        $this->assertEquals('bar2', $scope->import('bar2'));
    }
}