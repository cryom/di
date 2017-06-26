<?php

namespace vivace\di\example\base;

use vivace\di;
use vivace\di\example\base\service;

class Main extends di\Scope\Package
{
    public function __construct()
    {
        $this->class(service\B::class, ['name' => 'CLASS B']);
        $this->class(service\A::class, ['name' => 'CLASS A'])
            ->setUp(function (service\A $a) {
                $a->setData(['test' => 123]);
            });
    }

    /**
     * @return string
     */
    public function boot()
    {
        /** @var service\B $bClass */
        $bClass = $this->import(service\B::class);

        assert($bClass->getName() === 'CLASS B');
        assert($bClass->getA() instanceof service\A);
        assert($bClass->getA()->getData() === ['test' => 123]);
        return "OK\n";
    }
}

