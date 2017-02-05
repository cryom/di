<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 05.02.17
 * Time: 16:46
 */

namespace vivace\di\tests;

class Magneto
{

}

class Quicksilver extends Magneto
{

}

class Deadpool
{
    public function __construct(string $a, Magneto $b)
    {
    }
}

class Xavier
{
    public function __construct(Magneto $b = null)
    {

    }
}

