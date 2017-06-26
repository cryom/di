<?php

namespace vivace\di\example\base;

use vivace\di;
use vivace\di\example\base\module\api;
use vivace\di\example\base\service;

class Main extends di\Scope\Package
{
    public function __construct()
    {
        $this->use(new di\example\base\module\api\Main())
            ->insteadOf(api\service\Api::class, service\Api::class);

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
        /** @var service\B $b */
        $b = $this->import(service\B::class);
        /** @var api\ApiInterface $api */
        $api = $this->import(api\ApiInterface::class);
        $result = 'B->greeting: ' . $b->greeting() . "\n";
        $result .= 'A->do: ' . $b->getA()->do() . "\n";
        $result .= 'ApiInterface->getVersion: ' . get_class($api) . ' ' . $api->getVersion() . "\n";

        return $result;
    }
}

