<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 03.03.17
 * Time: 0:56
 */

namespace vivace\di\tests\Container;


use PHPUnit\Framework\TestCase;
use vivace\di\Container;
use vivace\di\InvalidArgumentError;
use vivace\di\Scope;
use vivace\di\Scope\Branch;

class ProxyTest extends TestCase
{
    public function testHas()
    {
        $proxy = new Container\Proxy(new Container\Base([
            'a' => $aF = function () {
                return 'a';
            },
        ]));

        $this->assertTrue($proxy->has('a'));
    }

    public function testGet()
    {
        $proxy = new Container\Proxy(new Container\Base([
            'b' => function () {
                return 'b';
            },
        ]));

        $this->assertEquals('b', call_user_func($proxy->get('b')));
    }

    public function testAlias()
    {
        $proxy = new Container\Proxy(new Container\Base([
            'a' => function () {
                return 'a';
            },
        ]));
        $proxy->as('a', 'b');

        $this->assertEquals(call_user_func($proxy->get('a')), call_user_func($proxy->get('b')));
    }

    public function testInsteadOf()
    {
        $scope = new Branch();
        $proxy = new Container\Proxy(new Container\Base([
            'a' => function (Scope $scope) {
                return $scope->import('b');
            },
            'b' => \vivace\di\wrap('b'),
            'z' => \vivace\di\wrap('z'),
        ]));
        $proxy->insteadOf('b', 'z');

        $this->assertEquals(call_user_func($proxy->get('z'), $scope), call_user_func($proxy->get('a'), $scope));
    }

    public function testPrimary()
    {
        $scope = new Branch([
            'a' => \vivace\di\wrap('a'),
        ]);
        $proxy = new Container\Proxy(new Container\Base([
            'a' => \vivace\di\wrap('a1'),
            'b' => function (Scope $scope) {
                return $scope->import('a');
            }
        ]));
        $proxy->primary('a');

        $scope = new Scope\Node($scope, $proxy);
        $this->assertEquals('a1', call_user_func($proxy->get('b'), $scope));

    }

    public function testInsteadFor()
    {
        $scope = new Branch([
            'a1' => \vivace\di\wrap('a1'),
            'b2' => \vivace\di\wrap('b2'),
        ]);
        $proxy = new Container\Proxy(new Container\Base([
            'a' => \vivace\di\wrap('a'),
            'b' => \vivace\di\wrap('b'),
            'c' => function (Scope $scope) {
                return $scope->import('a') . $scope->import('b');
            },
            'd' => function (Scope $scope) {
                return $scope->import('a') . $scope->import('b');
            },
            'e' => function (Scope $scope) {
                return $scope->import('e1') . $scope->import('e2');
            },
        ]));
        $proxy->insteadFor('d', [
            'a' => 'a1',
            'b' => 'b2',
        ]);
        $proxy->insteadFor('e', [
            'e1' => function () {
                return 've1';
            },
            'e2' => function (Scope $scope) {
                return $scope->import('d');
            },
        ]);
        $node = new Scope\Node($scope, $proxy);
        $this->assertEquals('ab', $node->import('c'));
        $this->assertEquals('a1b2', $node->import('d'));
        $this->assertEquals('ve1a1b2', $node->import('e'));

        $this->expectException(InvalidArgumentError::class);
        $proxy->insteadFor('e', [
            'e1' => null
        ]);

        $proxy->insteadFor('e', [
            'e1' => 123
        ]);

        $proxy->insteadFor('e', [
            'e1' => []
        ]);
    }
}