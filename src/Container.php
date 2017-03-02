<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 24.02.17
 * Time: 1:04
 */

namespace vivace\di;


use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $factories = [];

    public function __construct(array $factories = [])
    {
        $this->factories = $factories;
    }

    /**
     * @inheritdoc
     * @return callable
     */
    public function get($id): callable
    {
        if (!isset($this->factories[$id])) {
            throw new NotFoundError("$id not defined");
        }
        return wrap($this->factories[$id]);
    }

    /**
     * @inheritdoc
     * @return bool
     */
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
                    $this->factories[$id] = $factory;
                }
            }
        };
    }

    protected static function prepareFactory($value): callable
    {
        if (!is_callable($value)) {
            return function () use ($value) {
                return $value;
            };
        }
        return $value;
    }
}