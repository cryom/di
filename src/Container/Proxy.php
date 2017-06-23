<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 24.02.17
 * Time: 0:05
 */
namespace vivace\di\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use vivace\di\InvalidArgumentError;
use vivace\di\Proxiable;
use vivace\di\Scope;

class Proxy extends Base implements Proxiable
{
    /** @var  ContainerInterface */
    private $container;
    /** @var callable[] */
    private $primary = [];
    /** @var callable[][] */
    private $bounds = [];

    public function __construct(ContainerInterface $container, array $items = [])
    {
        parent::__construct($items);
        $this->container = $container;
    }

    /** @inheritdoc */
    public function as(string $sourceId, string $alias): Proxiable
    {
        $this->items[$alias] = $this->get($sourceId);
        return $this;
    }

    /** @inheritdoc */
    public function insteadOf(string $sourceId, string $delegateId): Proxiable
    {
        $this->primary[$sourceId] = function (Scope $scope) use ($delegateId) {
            $scope = new Scope\Node($scope, $this);
            return $scope->import($delegateId);
        };
        return $this;
    }

    public function get($id): callable
    {
        try {
            $factory = parent::get($id);
        } catch (NotFoundExceptionInterface $e) {
            $factory = \vivace\di\wrap($this->container->get($id));
        }
        if (empty($this->primary) && !isset($this->bounds[$id])) {
            return $factory;
        }
        return function (Scope $scope) use ($factory, $id) {
            $primaryFactories = array_merge($this->bounds[$id] ?? [], $this->primary);
            $scope = new Scope\Node(new Base($primaryFactories), $scope);

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
    public function primary(string $targetId): Proxiable
    {
        $this->primary[$targetId] = function (Scope $scope) use ($targetId) {
            $factory = $this->container->get($targetId);
            return call_user_func(\vivace\di\wrap($factory), $scope);
        };
        return $this;
    }

    /**
     * @param string $targetId
     * @param array $map
     * Map, where key is factory id and value should be type of callable or string.
     * If value is string, then will be import from scope
     * @return Proxiable
     */
    public function insteadFor(string $targetId, array $map): Proxiable
    {
        $factories = [];
        foreach ($map as $id => $delegate) {
            if (is_callable($delegate)) {
                $factories[$id] = $delegate;
            } elseif (is_string($delegate)) {
                $factories[$id] = function (Scope $scope) use ($delegate) {
                    return $scope->import($delegate);
                };
            } else {
                throw new InvalidArgumentError("Invalid value type. Value should be a callable or string");
            }
        }
        $this->bounds[$targetId] = $factories;
        return $this;
    }
}
