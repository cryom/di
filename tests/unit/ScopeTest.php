<?php


use vivace\di;

class ScopeTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @inheritdoc */
    protected function _before()
    {
    }

    /** @inheritdoc */
    protected function _after()
    {
    }

    /**
     * @return string
     */
    public function factory()
    {
        return 'i_factory';
    }

    /**
     * @param array $config
     * @return di\Scope
     */
    protected function newScope(array $config = []): di\Scope
    {
        return new class($config) extends di\Scope
        {
            public function __construct(array $config)
            {
                $config += ['export' => [], 'inherit' => []];
                foreach ($config['export'] as $id => $factory) {
                    if (!is_callable($factory)) {
                        $factory = function () use ($factory) {
                            return $factory;
                        };
                    }
                    $this->export($id, $factory);
                }

                foreach ($config['inherit'] as $config) {
                    $config += ['aliases' => []];
                    list('scope' => $scope, 'aliases' => $aliases) = $config;
                    $this->inherit($scope, $aliases);
                }
            }
        };
    }

    /**
     * @return di\Scope
     */
    protected function newDefaultScope(): di\Scope
    {
        return $this->newScope([
            'export' => [
                'foo' => [$this, 'factory']
            ]
        ]);
    }


    public function testExportIdentifierConflict()
    {
        $this->tester->expectException(di\error\IdentifierConflict::class, function () {
            $scope = new class extends di\Scope
            {
                public function __construct()
                {
                    $this->export('foo', function () {
                    });
                    $this->export('foo', function () {
                    });
                }
            };
        });
    }
    // tests
    /**
     *
     */
    public function testFetch()
    {
        $scope = $this->newDefaultScope();
        $this->tester->assertInstanceOf(Closure::class, $scope->fetch('foo'));
    }

    public function testRecursiveCall()
    {
        $scope = $this->newScope([
            'export' => [
                'foo' => function (di\type\Scope $scope) {
                    return $scope->import('foo');
                }
            ]
        ]);
        $this->tester->expectException(di\error\RecursiveDependency::class, function () use ($scope) {
            $scope->import('foo');
        });

    }

    public function testImport()
    {
        $scope = $this->newDefaultScope();

        $this->tester->assertEquals('i_factory', $scope->import('foo'));
    }

    public function testInherit()
    {
        $scope = $this->newScope([
            'inherit' => [
                ['scope' => $this->newDefaultScope()]
            ]
        ]);
        $this->tester->assertEquals('i_factory', $scope->import('foo'));

    }

    public function testInheritOverride()
    {
        $scope = $this->newScope([
            'export' => [
                'foo' => 'i_override_factory'
            ],
            'inherit' => [
                ['scope' => $this->newDefaultScope()]
            ]
        ]);

        $this->tester->assertEquals('i_override_factory', $scope->import('foo'));
    }

    public function testOverrideNestedDependencies()
    {
        $scope = $this->newScope([
            'export' => ['var' => 'normal'],
            'inherit' => [
                [
                    'scope' => $this->newScope([
                        'export' => [
                            'var' => 'default',
                            'bar' => function (di\type\Scope $scope) {
                                return $scope->import('var');
                            }
                        ],
                    ])
                ]
            ]
        ]);

        $this->tester->assertEquals('normal', $scope->import('bar'));
    }


    public function testBind()
    {

        $scope = $this->newScope([
            'export' => ['bar' => 'bar']
        ]);
        $factory = $this->newDefaultScope()->bind(function (di\type\Scope $scope) {
            return $scope->import('foo') . $scope->import('bar');
        });
        $this->assertEquals('i_factorybar', $factory($scope));
    }
}