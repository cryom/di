<?php

namespace vivace\di\example\base\service;

class B
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var A
     */
    private $a;

    public function __construct(string $name, A $a)
    {
        $this->name = $name;
        $this->a = $a;
    }

    public function greeting()
    {
        return 'Hello! ' . $this->getName();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return A
     */
    public function getA(): A
    {
        return $this->a;
    }
}