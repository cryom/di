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
        $this->tester->assertInstanceOf(Closure::class, $switcher->getProducer('foo'));
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

        $this->tester->assertInstanceOf(Closure::class, $main->getProducer('bar'));
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

        $this->tester->assertInstanceOf(Closure::class, $main->getProducer('foo'));
        $this->tester->assertEquals('boo', $main->import('foo'));
    }

    public function testDeepTest()
    {
        /** @var Scope $services1 */
        /** @var Scope $services2 */
        /** @var Scope $models */
        /** @var Scope $main */

        $services1 = $this->tester->newScope([
            'db' => 'db_service_1',
            'service1' => function (Scope $scope) {
                return 'service1_' . $scope->import('db');
            },
        ]);

        $services2 = $this->tester->newScope([
            'db' => 'db_service_2',
            'service21' => function (Scope $scope) {
                return 'service21_' . $scope->import('db');
            },
            'service22' => function (Scope $scope) {
                return 'service22_' . $scope->import('service21');
            },
        ]);

        $models = $this->tester->newScope([
            'db' => 'db_models',
            'models' => function (Scope $scope) {
                return 'model_with_' . $scope->import('db');
            },
            'deep' => function (Scope $scope) {
                return 'deep_' . $scope->import('service22');
            },
            'deep2' => function (Scope $scope) {
                return $scope->import('deep');
            },
            'deep3' => function (Scope $scope) {
                return $scope->import('deep2');
            },
        ]);

        $main = $this->tester->newScope(
            ['a' => 'a', 'b' => 'b'],
            [$services1, 'as' => ['db' => 'db_main']],
            [$services2, 'insteadOf' => ['db' => 'a']],
            [$models, 'insteadOf' => ['db' => 'b'], 'bind' => ['deep3' => new \vivace\di\Container(['deep2' => 'newDeep'])]]
        );

        $this->tester->assertEquals('db_service_1', $main->import('db'));
        $this->tester->assertEquals('db_service_1', $main->import('db_main'));
        $this->tester->assertEquals('service22_service21_a', $main->import('service22'));
        $this->tester->assertEquals('model_with_b', $main->import('models'));
        $this->tester->assertEquals('deep_service22_service21_a', $main->import('deep2'));
        $this->tester->assertEquals('newDeep', $main->import('deep3'));
    }
}