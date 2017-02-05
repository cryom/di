<?php

use Codeception\Util\Stub;
use vivace\di;

class MetaTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        if (!function_exists('testReflectFunction')) {
            function testReflectFunction($a, $b, $c)
            {

            }
        }
    }

    protected function _after()
    {
    }

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    public static function staticFunction($a, $b, $c)
    {

    }

    // tests
    public function testStaticFunctionByArray()
    {
        $meta = new di\Meta();
        $r = $meta->reflect([self::class, 'staticFunction']);

        $this->tester->assertInstanceOf(ReflectionFunctionAbstract::class, $r);

    }

    public function testStaticFunctionByString()
    {
        $r = (new di\Meta())->reflect('MetaTest::staticFunction');
        $this->tester->assertInstanceOf(ReflectionFunctionAbstract::class, $r);
    }

    public function testConstruct()
    {
        $r = (new di\Meta())->reflect(self::class);
        $this->tester->assertInstanceOf(ReflectionFunctionAbstract::class, $r);
    }

    public function testClosure()
    {
        $r = (new di\Meta())->reflect(function ($a, $b, $c) {

        });

        $this->tester->assertInstanceOf(ReflectionFunctionAbstract::class, $r);
        $this->tester->assertEquals(3, $r->getNumberOfParameters());
    }

    public function testFunction()
    {


        $r = (new di\Meta())->reflect('testReflectFunction');
        $this->tester->assertInstanceOf(ReflectionFunctionAbstract::class, $r);
        $this->tester->assertEquals(3, $r->getNumberOfParameters());
    }

    public function testNullResult()
    {
        $r = (new di\Meta())->reflect('undefinedFunction');
        $this->tester->assertNull($r);
    }

    public function testDependencies()
    {
        $r = (new di\Meta())->dependencies(function ($a, string $b, MetaTest $c, array $d = null, $e = []) {

        });
        $this->tester->assertInternalType('array', $r);
        $this->tester->assertCount(5, $r);

        foreach ($r as $item) {
            $this->tester->assertArrayHasKey('name', $item);
        }
        $this->tester->assertArrayNotHasKey('type', $r[0]);
        $this->tester->assertArrayNotHasKey('default', $r[0]);

        $this->tester->assertArrayHasKey('type', $r[1]);
        $this->tester->assertEquals('string', $r[1]['type']);
        $this->tester->assertArrayNotHasKey('default', $r[1]);

        $this->tester->assertArrayHasKey('type', $r[2]);
        $this->tester->assertEquals(MetaTest::class, $r[2]['type']);
        $this->tester->assertArrayNotHasKey('default', $r[2]);

        $this->tester->assertArrayHasKey('type', $r[3]);
        $this->tester->assertEquals('array', $r[3]['type']);
        $this->tester->assertArrayHasKey('default', $r[3]);
        $this->tester->assertNull($r[3]['default']);

        $this->tester->assertArrayHasKey('default', $r[4]);
        $this->tester->assertInternalType('array', $r[4]['default']);
        $this->tester->assertArrayNotHasKey('type', $r[4]);
    }

    public function testCaches()
    {
        $meta = Stub::make(di\Meta::class, array('reflect' => Stub::once()));
        $r1 = $meta->dependencies('MetaTest::staticFunction');
        $r2 = $meta->dependencies('MetaTest::staticFunction');
        $this->tester->assertInternalType('array', $r1);
        $this->tester->assertInternalType('array', $r2);
        $this->tester->assertEquals($r1, $r2);
    }
}