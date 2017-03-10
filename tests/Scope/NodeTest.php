<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 28.02.17
 * Time: 1:17
 */

namespace vivace\di\tests\Scope;


use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use vivace\di\Container;
use vivace\di\ImportFailureError;
use vivace\di\Scope;
use vivace\di\Scope\Branch;
use vivace\di\Scope\Node;

class NodeTest extends TestCase
{
    public function testHas()
    {
        $node = new Node(new Branch(['a' => 'a']), new Container\Base(['b' => 'b']));

        $this->assertTrue($node->has('a'));
        $this->assertTrue($node->has('b'));
        $this->assertFalse($node->has('c'));
    }

    public function testGet()
    {
        $node = new Node(new Branch(['a' => 'a']), new Container\Base(['b' => 'b']));
        $this->assertInternalType('callable', $node->get('a'));
        $this->assertInternalType('callable', $node->get('b'));
        $this->expectException(NotFoundExceptionInterface::class);
        $node->get('c');
    }

    public function testImport()
    {
        $node = new Node(
            new Branch([
                'a' => 'a',
                'c' => function (Scope $scope) {
                    return 'c' . $scope->import('b');
                },
                'e' => function (Scope $scope) {
                    return 'e1' . $scope->import('e');
                },
                'f' => function (Scope $scope) {
                    return $scope->import('f');
                },
            ]),
            new Container\Base([
                'b' => 'b',
                'd' => function (Scope $scope) {
                    return 'd' . $scope->import('c');
                },
                'e' => function (Scope $scope) {
                    return 'e2' . $scope->import('f');
                },
                'f' => function (Scope $scope) {
                    return 'f2';
                },
            ])
        );

        $this->assertEquals('a', $node->import('a'));
        $this->assertEquals('b', $node->import('b'));
        $this->assertEquals('cb', $node->import('c'));
        $this->assertEquals('dcb', $node->import('d'));
        $this->assertEquals('e1e2f2', $node->import('e'));

        $this->expectException(ImportFailureError::class);
        $node->import('zzz');
    }

    public function testDeeperDive()
    {
        $node = new Node(
            new Node(
                new Branch([
                    'a' => function(Scope $scope){
                        return 'a1' . $scope->import('a') . $scope->import('b');
                    }
                ]),
                new Node(
                    new Branch([
                        'a' => function(Scope $scope){
                            return 'a' . $scope->import('a');
                        }   
                    ]),
                    new Branch([
                        'b' => function(Scope $scope){
                            return 'b1' . $scope->import('b');
                        }   
                    ])
                )                
            ),
            new Node(
                new Branch([
                    'a' => function(Scope $scope){
                        return 'a2';
                    }   
                ]),
                new Branch([
                    'b' => function(Scope $scope){
                        return 'b2' . $scope->import('a');
                    }   
                ]),
                new Node(
                    new Branch([
                        'c' => function(Scope $scope){
                            return 'c' . $scope->import('a');
                        }
                    ])
                )
            )
        );

        $this->assertEquals('ca1ab1b2a2', $node->import('c'));
    }
}
