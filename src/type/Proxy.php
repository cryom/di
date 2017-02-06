<?php
namespace vivace\di\type;

interface Proxy extends Scope
{
    public function as ($source, $name): Proxy;

    public function insteadOf($source, $name): Proxy;
}
