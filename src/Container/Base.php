<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 28.02.17
 * Time: 1:52
 */

namespace vivace\di\Container;


use Psr\Container\ContainerInterface;
use vivace\di\NotFoundError;

class Base implements ContainerInterface
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     * @return callable
     */
    public function get($id): callable
    {
        if (!isset($this->items[$id])) {
            throw new NotFoundError("$id not defined");
        }
        return \vivace\di\wrap($this->items[$id]);
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->items[$id]);
    }
}