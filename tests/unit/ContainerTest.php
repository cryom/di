<?php


use vivace\di\type\Scope;

class ContainerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testExport()
    {
        $container = new \vivace\di\Container([
            'foo' => function () {
                return 'foo';
            },
            'bar' => function () {
                return 'bar';
            },
            'scalar' => 'value'
        ]);

        $this->tester->assertEquals('foo', $container->import('foo'));
        $this->tester->assertEquals('bar', $container->import('bar'));
        $this->tester->assertEquals('value', $container->import('scalar'));
    }

    public function testInherit()
    {
        $parent = new \vivace\di\Container(['foo' => function () {
            return 'foo';
        }]);
        $parent2 = new \vivace\di\Container(['bar' => function () {
            return 'bar';
        }]);
        $main = new \vivace\di\Container([], $parent, [$parent2]);

        $this->tester->assertEquals('foo', $main->import('foo'));
        $this->tester->assertEquals('bar', $main->import('bar'));
    }

    public function testInheritWithAliases()
    {

        $parent = new \vivace\di\Container(['foo' => function () {
            return 'foo';
        }]);

        $main = new \vivace\di\Container([], [$parent, 'as' => ['foo' => 'bar']]);

        $this->tester->assertEquals('foo', $main->import('foo'));
        $this->tester->assertEquals('foo', $main->import('bar'));
    }

    public function testInheritWithInsteadOf()
    {
        $parent = new \vivace\di\Container([
            'bar' => function () {
                return 'bar';
            },
            'foo' => function (Scope $scope) {
                return $scope->import('bar') . 'foo';
            },
        ]);

        $main = new \vivace\di\Container(
            [
                'newbar' => function () {
                    return 'newbar';
                }
            ],
            [$parent,
                'insteadOf' => ['bar' => 'newbar']
            ]
        );

        $this->tester->assertEquals('newbarfoo', $main->import('foo'));
    }
}