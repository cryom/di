<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 04.03.17
 * Time: 14:04
 */

namespace vivace\di\Scope;

use Psr\Container\ContainerInterface;
use vivace\di\BadDefinitionError;
use vivace\di\Container\Autowire;
use vivace\di\Container\Proxy;
use vivace\di\Factory;
use vivace\di\Factory\Instance;
use vivace\di\Proxiable;
use vivace\di\Resolver;
use vivace\di\Scope;

/**
 * Class Package
 * @package vivace\di\Scope
 */
abstract class Package implements Scope, Proxiable
{
    /** @var  Branch */
    private $branch;
    /** @var Proxy */
    private $proxy;
    /** @var Node */
    private $node;
    /** @var  Autowire */
    private $autowire;
    /** @var  Node */
    private $scope;
    /**
     * @return Branch
     */
    private function getBranch()
    {
        return $this->branch ?? $this->branch = new Branch([
                'vivace\di\Resolver' => function (Scope $scope) {
                    return new Resolver($scope);
                }
            ]);
    }

    /**
     * @return Proxy
     */
    private function getProxy()
    {
        return $this->proxy ?? $this->proxy = new Proxy($this->getNode());
    }

    /**
     * @return Autowire
     */
    private function getAutowire()
    {
        return $this->autowire ?? $this->autowire = new Autowire();
    }

    /**
     * Node used for extending other containers
     * @return Node
     * @see use()
     */
    private function getNode()
    {
        return $this->node ?? $this->node = new Node($this->getBranch(), $this->getAutowire());
    }

    /**
     * @return Node
     */
    private function getScope()
    {
        return $this->scope ?? $this->scope = new Node($this->getProxy());
    }

    /**
     * @param string $id
     * @param callable $factory
     * @throws BadDefinitionError
     */
    final protected function export(string $id, callable $factory)
    {
        if ($this->getBranch()->has($id)) {
            throw new BadDefinitionError("Factory $id has been defined");
        }
        $this->getBranch()->set($id, $factory);
    }

    /**
     * @param ContainerInterface $container
     * @return Proxiable
     */
    final protected function use (ContainerInterface $container): Proxiable
    {
        if (!$container instanceof Proxiable) {
            $container = new Proxy($container);
        }
        $this->getNode()->append($container);
        return $container;
    }


    /** @inheritdoc */
    public function import(string $id)
    {
        return $this->getScope()->import($id);
    }

    /** @inheritdoc */
    public function as (string $sourceId, string $alias): Proxiable
    {
        return $this->getProxy()->as($sourceId, $alias);
    }

    /** @inheritdoc */
    public function insteadOf(string $sourceId, string $delegateId): Proxiable
    {
        return $this->getProxy()->insteadOf($sourceId, $delegateId);
    }

    /** @inheritdoc */
    public function insteadFor(string $targetId, array $map): Proxiable
    {
        return $this->getProxy()->insteadFor($targetId, $map);
    }

    /**
     * @param string $id
     * @return callable
     */
    public function get($id)
    {
        return $this->getScope()->get($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return $this->getScope()->has($id);
    }

    /**
     * @param string $className
     * @param array $arguments
     * @return Factory
     */
    protected function class(string $className, array $arguments = []): Factory
    {
        $factory = new Instance($className, $arguments);
        $this->export($className, $factory);
        return $factory;
    }

    /**
     * @param string $interfaceName
     * @param string $className
     * @param array $arguments
     * @return Factory
     */
    protected function interface(string $interfaceName, string $className, array $arguments = []): Factory
    {
        $factory = new Instance($className, $arguments);
        $this->export($interfaceName, $factory);
        return $factory;
    }
}
