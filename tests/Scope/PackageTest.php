<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 04.03.17
 * Time: 14:06
 */

namespace vivace\di\tests\Scope;


use PHPUnit\Framework\TestCase;
use vivace\di\Container\Base;
use vivace\di\ImportFailureError;
use vivace\di\Scope;
use vivace\di\Scope\Package;

class PackageTest extends TestCase
{
    public function testHas()
    {
        $pkg = new class extends Package
        {
            public function __construct()
            {
                $this->export('a', \vivace\di\wrap('a'));
                $this->export('b', \vivace\di\wrap('b'));
                $this->use(new Base([
                    'd' => function () {
                        return 'd';
                    },
                ]));
            }
        };
        $this->assertTrue($pkg->has('a'));
        $this->assertTrue($pkg->has('b'));
        $this->assertTrue($pkg->has('d'));
        $this->assertFalse($pkg->has('c'));
    }

    public function testGet()
    {
        $pkg = new class extends Package
        {
            public function __construct()
            {
                $this->export('a', \vivace\di\wrap('a'));
                $this->export('b', \vivace\di\wrap('b'));
                $this->use(new Base([
                    'd' => function (Scope $scope) {
                        return 'd' . $scope->import('a');
                    },
                ]));
            }
        };
        $this->assertEquals('a', call_user_func($pkg->get('a'), $pkg));
        $this->assertEquals('b', call_user_func($pkg->get('b'), $pkg));
        $this->assertEquals('da', call_user_func($pkg->get('d'), $pkg));
    }

    public function testImport()
    {
        $pkg = new class extends Package
        {
            public function __construct()
            {
                $this->export('a', \vivace\di\wrap('a'));
                $this->export('b', \vivace\di\wrap('b'));
                $this->use(new Base([
                    'd' => function (Scope $scope) {
                        return 'd' . $scope->import('a');
                    },
                ]));
            }
        };

        $this->assertEquals('a', $pkg->import('a'));
        $this->assertEquals('b', $pkg->import('b'));
        $this->assertEquals('da', $pkg->import('d'));
        $this->expectException(ImportFailureError::class);
        $pkg->import('ddd');
    }
}