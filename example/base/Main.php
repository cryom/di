<?php

namespace vivace\di\example\base;

use vivace\di;
use vivace\di\example\base\service\A;

class Main extends di\Scope\Package
{
    public function __construct()
    {
        $this->use(new di\example\base\module\api\Main());

        $this->class('vivace\di\example\base\service\B', ['name' => 'CLASS B']);
        $this->class('vivace\di\example\base\service\A', ['name' => 'CLASS A'])
            ->setUp(function (A $a) {
                $a->setData(['test' => 123]);
            });
    }

    /**
     * @return string
     */
    public function boot()
    {
        /** @var di\example\base\service\B $obj */
        $obj = $this->import('vivace\di\example\base\service\B');
        $result = $obj->greeting();
        return $result . $obj->getA()->do();
    }
}

