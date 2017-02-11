<?php


class AutowireTest extends \Codeception\Test\Unit
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
    public function testCreateNew()
    {
        /** @var \vivace\di\type\Scope $scope */
        $scope = $this->tester->newScope([], new \vivace\di\AutoWire());

        $this->tester->assertInstanceOf(\vivace\di\tests\Magneto::class, $scope->import(\vivace\di\tests\Magneto::class));
    }

    public function testExceptionIfNotClass()
    {
        $this->tester->expectException(\vivace\di\error\Undefined::class, function () {
            $scope = $this->tester->newScope([], new \vivace\di\AutoWire());
            $scope->import('Undefined');
        });
    }
}