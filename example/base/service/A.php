<?php

namespace vivace\di\example\base\service;

class A
{
    /**
     * @var string
     */
    private $name;
    private $data;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData(){
        return $this->data;
    }
}