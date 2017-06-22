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

    public function do()
    {
        return 'This is ' . $this->name . "!\nData " . json_encode($this->data);
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }
}