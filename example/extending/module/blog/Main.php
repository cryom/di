<?php

namespace vivace\di\example\extending\module\blog;

use vivace\di\example\extending\service\ViewInterface;
use vivace\di\Scope\Package;

class Main extends Package
{
    public function __construct()
    {
        $this->interface(ViewInterface::class, service\View::class, ['tpl' => 'blog']);
    }

}
