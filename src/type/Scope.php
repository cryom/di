<?php
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
    public function getProducer(string $id): \Closure;

    /**
     * @param callable $producer
     * @return callable
     */
    public function bindTo(callable $producer): callable;


}
