<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 26.02.17
 * Time: 22:47
 */

namespace vivace\di\tests\fixture;

class Emp{
}

class Foo
{
    /** @var null */
    public $val;

    public function __construct($val = null)
    {
        $this->val = $val;
    }
}

class Foo2 extends Foo
{
    public function __construct()
    {
        parent::__construct('foo2');
    }
}
class Bar
{
    /** @var Foo */
    public $val;
    /** @var null */
    public $val1;

    public function __construct(Foo $val, $val1 = 'default_value')
    {
        $this->val = $val;
        $this->val1 = $val1;
    }
}

interface BazInterface
{

}


class Baz implements BazInterface
{

}

class Baz2 implements BazInterface
{

}