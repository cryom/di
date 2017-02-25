<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 24.02.17
 * Time: 1:04
 */

namespace vivace\di;


use Psr\Container\ContainerInterface;
use vivace\di\exception;

abstract class Container implements ContainerInterface
{
    protected $factories = [];

    /** @inheritdoc */
    public function get($id): callable
    {
        if (!isset($this->factories[$id])) {
            throw new exception\NotFound("$id not defined");
        }
        return $this->factories[$id];
    }

    /** @inheritdoc */
    public function has($id): bool
    {
        return isset($this->factories[$id]);
    }

    /**
     * @param iterable $factories
     * @return Container
     */
    public static function new(iterable $factories)
    {
        return new class($factories) extends Container
        {
            public function __construct($factories)
            {
                foreach ($factories as $id => $factory) {
                    if (!is_callable($factory)) {
                        throw new exception\BadDefinition("Factory $id must be callable.");
                    }
                    $this->factories[$id] = $factory;
                }
            }
        };
    }
}