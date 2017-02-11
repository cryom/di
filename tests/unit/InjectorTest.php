<?php

use vivace\di\tests\Magneto;
use vivace\di\tests\Quicksilver;
use vivace\di\tests\Xavier;

class InjectorTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        require_once __DIR__ . '/../fixture/entities.php';
    }

    protected function _after()
    {
    }

    // tests
    public function testResolveByClassName()
    {
        $meta = new \vivace\di\Meta();
        /** @var \vivace\di\type\Scope $scope */
        $scope = $this->tester->newScope([Magneto::class => function () {
            return new Magneto();
        }]);
        $injector = new \vivace\di\Injector($scope, $meta);
        $result = $injector->resolve(function (Magneto $object) {

        });

        $this->tester->assertInstanceOf(Magneto::class, $result[0]);
    }

    public function testResolveByPropertyName()
    {
        $meta = new \vivace\di\Meta();
        $scope = $this->tester->newScope(['magneto' => function () {
            return new Magneto();
        }]);
        $injector = new \vivace\di\Injector($scope, $meta);
        $result = $injector->resolve(function (Magneto $magneto) {

        });
        $this->tester->assertInstanceOf(Magneto::class, $result[0]);

    }

    public function testResolveWithDefaultValue()
    {
        $meta = new \vivace\di\Meta();
        $scope = $this->tester->newScope([]);
        $injector = new \vivace\di\Injector($scope, $meta);
        $result = $injector->resolve(function ($magneto = 123) {

        });
        $this->tester->assertEquals(123, $result[0]);

    }

    public function testCatchNotResolved()
    {
        $this->tester->expectException(\vivace\di\error\NotResolved::class, function () {
            $injector = new \vivace\di\Injector($this->tester->newScope([]), new \vivace\di\Meta());
            $injector->resolve(function (string $a) {

            });
        });
    }

    public function testReturnEmpty()
    {
        $injector = new \vivace\di\Injector(
            $this->tester->newScope([]),
            new \vivace\di\Meta()
        );

        $result = $injector->resolve(Magneto::class);
        $this->tester->assertInternalType('array', $result);
        $this->tester->assertEmpty($result);
    }

    public function testManyDependencies()
    {
        $injector = new \vivace\di\Injector(
            $this->tester->newScope([
                'a' => function () {
                    return 'a';
                },
                Magneto::class => function () {
                    return new Magneto();
                }
            ]),
            new \vivace\di\Meta()
        );

        $result = $injector->resolve(\vivace\di\tests\Deadpool::class);
        $this->tester->assertCount(2, $result);
        $this->tester->assertEquals('a', $result[0]);
        $this->tester->assertInstanceOf(Magneto::class, $result[1]);
    }

    public function testPriorityForType()
    {
        $injector = new \vivace\di\Injector(
            $this->tester->newScope([
                'b' => function () {
                    return new Magneto();
                },
                Magneto::class => function () {
                    return new Quicksilver();
                }
            ]),
            new \vivace\di\Meta()
        );

        $result = $injector->resolve(Xavier::class);
        $this->tester->assertInstanceOf(Quicksilver::class, $result[0]);

    }

    public function testPriorityForPropertyName()
    {
        $injector = new \vivace\di\Injector(
            $this->tester->newScope([
                'b' => function () {
                    return new Quicksilver();
                }
            ]),
            new \vivace\di\Meta()
        );

        $result = $injector->resolve(Xavier::class);
        $this->tester->assertInstanceOf(Quicksilver::class, $result[0]);
    }

    public function testNew()
    {
        /** @var \vivace\di\type\Scope $scope */
        $scope = $this->tester->newScope([
            \vivace\di\tests\Deadpool::class => function (\vivace\di\type\Scope $scope) {
                return new \vivace\di\tests\Deadpool('val', $scope->import(Magneto::class));
            },
            Magneto::class => function () {
                return new Magneto();
            },
        ]);

        $injector = new \vivace\di\Injector($scope, new \vivace\di\Meta());
        $object = $injector->new(\vivace\di\tests\Wolverine::class);
        $this->assertInstanceOf(\vivace\di\tests\Wolverine::class, $object);
        $object = $injector->new(\vivace\di\tests\X23::class, [
            Xavier::class => function () {
                return new Xavier();
            },
        ]);
        $this->assertInstanceOf(\vivace\di\tests\X23::class, $object);
    }

    public function testCall()
    {
        $injector = new \vivace\di\Injector(new \vivace\di\Container([
            Magneto::class => function () {
                return new Magneto();
            },
        ]), new \vivace\di\Meta());

        $result = $injector->call(function ($a, Magneto $magneto) {
            return [$a, $magneto];
        }, ['a' => 1]);

        $this->tester->assertEquals(1, $result[0]);
        $this->tester->assertInstanceOf(Magneto::class, $result[1]);
    }
}
