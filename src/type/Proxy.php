<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 04.02.17
 * Time: 19:17
 */

namespace vivace\di\type;


interface Proxy extends Scope
{
    public function as ($source, $name): Proxy;

    public function insteadOf($source, $name): Proxy;
}