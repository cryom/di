<?php
namespace vivace\di\Scope;

use Psr\Container\ContainerInterface;
use vivace\di\ImportFailureError;
use vivace\di\Scope;

class Node implements Scope
{
    private $stack = [];
    /** @var  ContainerInterface[] */
    private $secondary = [];
    /** @var ContainerInterface[] */
    private $primary = [];

    public function __construct(ContainerInterface ...$containers)
    {
        $this->primary = $containers;
    }


    /** @inheritdoc */
    public function get($id): ?callable
    {
        if ($this->secondary) {
            $containers = array_merge($this->primary, $this->secondary);
        } else {
            $containers = $this->primary;
        }
        foreach ($containers as $container) {
            if (isset($this->stack[$id]) && in_array($container, $this->stack[$id], true)) {
                continue;
            }
            if (!$container->has($id)) {
                continue;
            }
            if (null === ($factory = $container->get($id))) {
                break;
            }
            return function (Scope $scope) use ($id, $factory, $container) {
                $this->stack[$id][] = $container;
                $factory = \vivace\di\wrap($factory);
                $result = call_user_func($factory, $scope);
                array_pop($this->stack[$id]);
                if (empty($this->stack[$id])) {
                    unset($this->stack[$id]);
                }
                return $result;
            };
        }
        return null;
    }

    /** @inheritdoc */
    public function has($id): bool
    {
        if ($this->secondary) {
            $containers = array_merge($this->primary, $this->secondary);
        } else {
            $containers = $this->primary;
        }
        foreach ($containers as $container) {
            if ($container->has($id)) {
                return true;
            }
        }
        return false;
    }

    /** @inheritdoc */
    public function import(string $id)
    {
        if (null === ($factory = $this->get($id))) {
            throw new ImportFailureError("Undefined $id");
        }

        return call_user_func($factory, $this);
    }

    public function append(ContainerInterface $scope, bool $primary = true)
    {
        if ($primary) {
            $this->primary[] = $scope;
        } else {
            $this->secondary[] = $scope;
        }
    }

    public function prepend(ContainerInterface $scope, bool $primary = true)
    {
        if ($primary) {
            array_unshift($this->primary, $scope);
        } else {
            array_unshift($this->secondary, $scope);
        }
    }
}
