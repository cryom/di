<?php


use vivace\di\type\Scope;

class ProxyTest extends \Codeception\Test\Unit
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

    public function testFetch()
    {
        $parents = $this->tester->newScope(['foo' => function () {
                return 'foo';
            }]
        );
        $switcher = new \vivace\di\Proxy($parents);
        $this->tester->assertInstanceOf(Closure::class, $switcher->fetch('foo'));
    }

    public function testImport()
    {
        $parents = $this->tester->newScope(
            ['foo' => function () {
                return 'foo';
            }]
        );
        $switcher = new \vivace\di\Proxy($parents);
        $this->tester->assertEquals('foo', $switcher->import('foo'));
    }

    // tests
    public function testAlias()
    {
        $parents = $this->tester->newScope(
            [
                'foo' => function (Scope $scope) {
                    return $scope->import('baz');
                },
                'baz' => 'baz'
            ]
        );
        $switcher = new \vivace\di\Proxy($parents);
        $switcher->as('foo', 'bar');
        $main = $this->tester->newScope([], $switcher);

        $this->tester->assertInstanceOf(Closure::class, $main->fetch('bar'));
        $this->tester->assertEquals('baz', $main->import('bar'));
    }

    public function testInstead()
    {
        $scope = $this->tester->newScope(
            [
                'foo' => function (Scope $scope) {
                    return $scope->import('bar');
                },
                'bar' => 'bar'
            ]
        );
        $switcher = new \vivace\di\Proxy($scope);
        $switcher->insteadOf('bar', 'boo');

        $main = $this->tester->newScope(['boo' => 'boo'], $switcher);

        $this->tester->assertInstanceOf(Closure::class, $main->fetch('foo'));
        $this->tester->assertEquals('boo', $main->import('foo'));
    }
}