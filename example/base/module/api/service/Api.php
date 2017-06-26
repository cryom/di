<?php

namespace vivace\di\example\base\module\api\service;

class Api implements ApiInterface
{
    public $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getVersion()
    {
        return '1.0';
    }
}