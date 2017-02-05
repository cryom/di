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
        $this->tester->assertInstanceOf(Closure::class, $scope->fetch('bar'));
        $this->tester->assertInstanceOf(Closure::class, $scope->fetch('foo'));
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

        $factory = $scope->bind(function (\vivace\di\type\Scope $scope) {
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
}