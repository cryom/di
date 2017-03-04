<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 03.03.17
 * Time: 0:09
 */

namespace vivace\di\tests\Container;


use PHPUnit\Framework\TestCase;
use vivace\di\Container\Base;

class BaseTest extends TestCase
{
    public function testHas()
    {
        $c = new Base([
            'd' => 'd',
            'f' => 'f',
        ]);

        $this->assertTrue($c->has('d'));
        $this->assertTrue($c->has('f'));
    }

    public function testGet()
    {
        $c = new Base([
            'd' => $dF = function () {
                return 'd';
            },
            'f' => $fF = function () {
                return 'f';
            },
        ]);

        $this->assertEquals($dF, $c->get('d'));
        $this->assertEquals($fF, $c->get('f'));
    }
}