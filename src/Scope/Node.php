<?php
namespace vivace\di\Scope;

use Psr\Container\ContainerInterface;
use vivace\di\ImportFailureError;
use vivace\di\Scope;

class Node implements Scope
{
    private $stack = [];
    /** @var ContainerInterface[] */
    protected $containers = [];

    public function __construct(ContainerInterface ...$containers)
    {
        $this->containers = $containers;
    }

    private function isStacked($id, $container)
    {
        return isset($this->stack[$id]) && in_array($container, $this->stack[$id], true);
    }

    private function stackBegin($id, $container)
    {
        $this->stack[$id][] = $container;
    }

    private function stackEnd($id)
    {
        array_pop($this->stack[$id]);
        if (empty($this->stack[$id])) {
            unset($this->stack[$id]);
        }
    }
    /** @inheritdoc */
    public function get($id): ?callable
    {
        foreach ($this->containers as $container) {
            if ($this->isStacked($id, $container) || !$container->has($id)) {
                continue;
            }
            $factory = $container->get($id);
            if ($factory === null) {
                break;
            }
            return function (Scope $scope) use ($id, $factory, $container) {
                $this->stackBegin($id, $container);
                $result = call_user_func(
                    \vivace\di\wrap($factory),
                    $scope
                );
                $this->stackEnd($id);
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

    public function append(ContainerInterface $scope): void
    {
        $this->containers[] = $scope;
    }

    public function prepend(ContainerInterface $scope): void
    {
        array_unshift($this->containers, $scope);
    }
}
