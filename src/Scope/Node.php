<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 28.02.17
 * Time: 0:46
 */

namespace vivace\di\Scope;


use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use vivace\di\ImportFailureError;
use vivace\di\NotFoundError;
use vivace\di\Scope;

class Node implements Scope
{
    private $stack = [];
    /** @var ContainerInterface[] */
    private $containers;

    public function __construct(ContainerInterface ...$containers)
    {
        $this->containers = $containers;
    }


    /** @inheritdoc */
    public function get($id): callable
    {
        foreach ($this->containers as $container) {
            if (isset($this->stack[$id]) && in_array($container, $this->stack[$id], true)) {
                continue;
            }
            try {
                $factory = $container->get($id);
            } catch (NotFoundExceptionInterface $e) {
                continue;
            }
            return function (Scope $scope) use ($id, $factory, $container) {
                $this->stack[$id][] = $container;
                $result = call_user_func(\vivace\di\wrap($factory), $scope);
                array_pop($this->stack[$id]);
                if (empty($this->stack[$id])) {
                    unset($this->stack[$id]);
                }
                return $result;
            };
        }
        throw new NotFoundError("Item $id not found.");
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
        try {
            $factory = $this->get($id);
        } catch (NotFoundExceptionInterface $e) {
            throw new ImportFailureError($e->getMessage(), 0, $e);
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