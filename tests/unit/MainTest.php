<?php


class MainTest extends \Codeception\Test\Unit
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
    public function testVersion()
    {
        $this->tester->assertEquals('0.0.1', vivace\di\VERSION);
    }
}