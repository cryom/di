<?php

namespace vivace\di\example\base\module\api;

use vivace\di\example\base\module\api\service\Api;
use vivace\di\Scope\Package;

class Main extends Package
{
    public function __construct()
    {
        $this->as(Api::class, ApiInterface::class);
    }

}
