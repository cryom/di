<?php

namespace vivace\di\example\base\module\api;

use vivace\di\example\base\module\api\service\Api;
use vivace\di\example\base\module\api\service\ApiInterface;
use vivace\di\Scope\Package;

class Main extends Package
{
    public function __construct()
    {
        parent::__construct();
        /* The following definitions can be omitted, because the vivace\di\Scope\Package uses
           the vivace\di\Container\Autowire container, which automates the.*/
        $this->as(Api::class, ApiInterface::class);

    }

}