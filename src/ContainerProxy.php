<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 24.02.17
 * Time: 0:05
 */

namespace vivace\di;


use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerProxy extends Container implements Proxiable
{
    /** @var  ContainerInterface */
    private $container;
    /** @var callable[] */
    private $primary = [];
    /** @var Container[] */
    private $bounds = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /** @inheritdoc */
    public function as(string $sourceId, string $alias): Proxiable
    {
        $this->factories[$alias] = $this->get($sourceId);
        return $this;
    }

    /** @inheritdoc */
    public function insteadOf(string $sourceId, string $delegateId): Proxiable
    {
        $this->primary[$sourceId] = function (Scope $scope) use ($delegateId) {
            return $scope->import($delegateId);
        };
        return $this;
    }

    public function get($id): callable
    {
        try {
            $factory = parent::get($id);
        } catch (NotFoundExceptionInterface $e) {
            $factory = $this->container->get($id);
        }
        if (empty($this->primary) && !isset($this->bounds[$id])) {
            return $factory;
        }
        return function (Scope $scope) use ($factory, $id) {
            $primaryFactories = array_merge($this->bounds[$id] ?? [], $this->primary);
            $scope = new Composite(Container::new($primaryFactories), $scope);

            return call_user_func($factory, $scope);
        };
    }

    public function has($id): bool
    {
        return parent::has($id) || $this->container->has($id);
    }

    /**
     * Revoke redefinition
     * @param string $targetId
     * @return mixed
     */
    public function final(string $targetId): Proxiable
    {
        $this->primary[$targetId] = function (Scope $scope) use ($targetId) {
            $factory = $this->container->get($targetId);
            return call_user_func($factory, $scope);
        };
        return $this;
    }

    /**
     * @param string $targetId
     * @param array $map
     * @return Proxiable
     */
    public function insteadFor(string $targetId, array $map): Proxiable
    {
        $factories = [];
        foreach ($map as $id => $delegateId) {
            $factories[$id] = function (Scope $scope) use ($delegateId) {
                return $scope->import($delegateId);
            };
        }
        $this->bounds[$targetId] = $factories;
        return $this;
    }
}