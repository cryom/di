<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 24.02.17
 * Time: 19:21
 */

namespace vivace\di\tests;


use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use vivace\di\Composite;
use vivace\di\Container;

class CompositeTest extends TestCase
{
    public function testHas()
    {
        $composite = new Composite(
            Container::new([
                'a' => function () {
                    return 'a';
                },
            ]),
            Container::new([
                'b' => function () {
                    return 'b';
                },
            ])
        );
        $this->assertTrue($composite->has('a'));
        $this->assertTrue($composite->has('b'));
        $this->assertFalse($composite->has('c'));
    }

    public function testGet()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $composite = new Composite(
            Container::new([
                'a' => $aF = function () {
                    return 'a';
                },
            ]),
            Container::new([
                'b' => $bF = function () {
                    return 'b';
                },
            ])
        );
        $this->assertEquals($aF, $composite->get('a'));
        $this->assertEquals($bF, $composite->get('b'));
        $composite->get('c');
    }

    public function testImportScalarValue()
    {
        $container = new class implements ContainerInterface
        {
            public function get($id)
            {
                return 123;
            }

            public function has($id)
            {
                return true;
            }
        };

        $composite = new Composite($container);

        $this->assertEquals(123, $composite->import('id'));
    }
}