<?php

namespace vivace\di;

use vivace\di\Meta\OfClass;

class Meta
{
    /** @var OfClass[string] */
    private $classes = [];

    public function class(string $name)
    {
        return $this->classes[$name] ?? $this->classes[$name] = new OfClass($name);
    }
}