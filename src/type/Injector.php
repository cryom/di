<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 11.02.17
 * Time: 12:57
 */

namespace vivace\di\type;


/**
 * Interface Injector
 * @package vivace\di\type
 */
interface Injector
{
    /**
     * @param $target
     * @param array $arguments
     * @return array
     */
    public function resolve($target, array $arguments = []): array;

    /**
     * @param string $className
     * @param array $arguments
     * @return mixed
     */
    public function new(string $className, array $arguments = []);

    /**
     * @param callable $function
     * @param array $arguments
     * @return mixed
     */
    public function call(callable $function, array $arguments = []);
}