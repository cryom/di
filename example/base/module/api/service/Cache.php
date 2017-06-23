<?php

namespace vivace\di\example\base\module\api\service;

class Cache
{
    /**
     * @var string
     */
    public $path;

    public function __construct($path = './tmp')
    {
        $this->path = $path;
    }
}