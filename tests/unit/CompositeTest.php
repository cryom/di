<?php


class CompositeTest extends \Codeception\Test\Unit
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

    protected function getScope(): \vivace\di\type\Composite
    {
        return new \vivace\di\Composite(
            $this->tester->newScope(['foo' => 'foo']),
            $this->tester->newScope(['bar' => 'bar'])
        );

    }

    public function testFetch()
    {
        $scope = $this->getScope();
        $this->tester->assertInstanceOf(Closure::class, $scope->getProducer('bar'));
        $this->tester->assertInstanceOf(Closure::class, $scope->getProducer('foo'));
    }

    // tests
    public function testImport()
    {
        $scope = $this->getScope();

        $this->tester->assertEquals('bar', $scope->import('bar'));
        $this->tester->assertEquals('foo', $scope->import('foo'));
    }

    public function testBind()
    {
        $scope = $this->getScope();

        $mainScope = $this->tester->newScope(['baz' => 'baz']);

        $factory = $scope->bindTo(function (\vivace\di\type\Scope $scope) {
            return $scope->import('bar') . $scope->import('baz');
        });


        $result = $factory($mainScope);

        $this->tester->assertEquals('barbaz', $result);
    }

    public function testAppend()
    {
        $scope = $this->getScope();
        $scope->append($this->tester->newScope(['aza' => 'aza']));

        $this->tester->assertEquals('aza', $scope->import('aza'));
    }

    public function testPrepend()
    {
        $scope = $this->getScope();
        $scope->prepend($this->tester->newScope(['bar' => 'newbar']));
        $this->tester->assertEquals('newbar', $scope->import('bar'));
    }

    public function testException()
    {
        $this->tester->expectException(\vivace\di\error\Undefined::class, function(){
            $this->getScope()->getProducer('undefined');
        });
    }

    public function testFlowImport()
    {
        $scope1 = $this->tester->newScope([
            'a' => function (\vivace\di\type\Scope $scope) {
                return '1' . $scope->import('a');
            },
        ]);
        $scope2 = $this->tester->newScope([
            'a' => function (\vivace\di\type\Scope $scope) {
                return '2' . $scope->import('a');
            },
        ]);
        $composite1 = new \vivace\di\Composite(
            $this->tester->newScope([
                'a' => function (\vivace\di\type\Scope $scope) {
                    return '3' . $scope->import('a');
                },
            ]),
            $this->tester->newScope(['a' => '4'])
        );
        $composite = new \vivace\di\Composite($scope1, $scope2, $composite1);

        $this->tester->assertEquals('1234', $composite->import('a'));
    }
}