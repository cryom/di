<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 26.02.17
 * Time: 22:46
 */

namespace vivace\di\tests;


use PHPUnit\Framework\TestCase;
use vivace\di\Resolver;
use vivace\di\NotResolvedError;
use vivace\di\Package;
use vivace\di\tests\fixture\Bar;
use vivace\di\tests\fixture\Emp;
use vivace\di\tests\fixture\Foo;

class ResolverTest extends TestCase
{

    public function setUp()
    {
        require_once __DIR__ . '/fixture/classes.php';
    }

    public function testResolve()
    {

        $resolver = new Resolver(Package::new([]));

        $this->assertEquals([null], $resolver->resolve(Foo::class));
        $this->assertEquals([], $resolver->resolve(Emp::class));
        $foo = new Foo(123);
        $this->assertEquals([$foo, 'default_value'], $resolver->resolve(Bar::class, [$foo]));
        $this->assertEquals([$foo, 'default_value'], $resolver->resolve(Bar::class, ['val' => $foo]));
        $this->assertEquals([$foo, 'value'], $resolver->resolve(Bar::class, ['val1' => 'value', 'val' => $foo]));
        $this->assertEquals([$foo, 'value'], $resolver->resolve(Bar::class, ['val1' => 'value', Foo::class => $foo]));

        $resolver = new Resolver(Package::new([Foo::class => function()use($foo){
            return $foo;
        }]));
        $this->assertEquals([$foo, 'default_value'], $resolver(Bar::class));
        $this->assertEquals([$foo, 'value'], $resolver->resolve(Bar::class, ['val1' => 'value']));

        $foo2 = new Foo(312);
        $this->assertEquals([$foo2, 'value'], $resolver->resolve(Bar::class, ['val1' => 'value', 0 => $foo2]));

        $this->expectException(NotResolvedError::class);
        $resolver = new Resolver(Package::new([]));
        $resolver->resolve(Bar::class);
    }


}