<?php
namespace vivace\di\Scope;

use Psr\Container\ContainerInterface;
use vivace\di\ImportFailureError;
use vivace\di\Scope;

class Node implements Scope
{
    private $stack = [];
    /** @var ContainerInterface[] */
    private $containers = [];

    public function __construct(ContainerInterface ...$containers)
    {
        $this->containers = $containers;
    }


    /** @inheritdoc */
    public function get($id): ?callable
    {
        foreach ($this->containers as $container) {
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
        foreach ($this->containers as $container) {
            if ($container->has($id)) {
                return true;
            }
        }
        return false;
    }

    /** @inheritdoc */
    public function import(string $id)
    {
        $factory = $this->get($id);
        if ($factory === null) {
            throw new ImportFailureError("Undefined $id");
        }

        return call_user_func($factory, $this);
    }

    public function append(ContainerInterface $scope)
    {
        $this->containers[] = $scope;
    }

    public function prepend(ContainerInterface $scope)
    {
        array_unshift($this->containers, $scope);
    }
}
