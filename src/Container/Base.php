<?php
namespace vivace\di\Container;

use Psr\Container\ContainerInterface;
use function vivace\di\wrap;

class Base implements ContainerInterface
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     * @return callable|null
     */
    public function get($id): ?callable
    {
        return isset($this->items[$id]) ? wrap($this->items[$id]) : null;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->items[$id]);
    }

    public function set(string $id, $value)
    {
        $this->items[$id] = $value;
    }

    public function delete(string $id)
    {
        unset($this->items[$id]);
    }
}
