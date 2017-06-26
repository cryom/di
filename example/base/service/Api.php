<?php

namespace vivace\di\example\base\service;

class Api extends \vivace\di\example\base\module\api\service\Api
{
    /**
     * @var B
     */
    public $b;

    public function __construct($cache, B $b)
    {
        parent::__construct($cache);
        $this->b = $b;
    }

    public function getVersion()
    {
        return '1.1';
    }


}