<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 02.02.17
 * Time: 22:55
 */

namespace vivace\di\type;


use vivace\di\error\RecursiveDependency;
use vivace\di\error\Undefined;

/**
 * Interface Scope
 * @package vivace\di\type
 */
interface Scope
{
    /**
     * @param string $id
     * @return mixed
     * @throws RecursiveDependency
     * @throws Undefined
     */
    public function import(string $id);

    /**
     * @param string $id
     * @return \Closure
     */
    public function fetch(string $id): \Closure;

    /**
     * @param callable $factory
     * @return callable
     */
    public function bind(callable $factory): callable;


}