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
use vivace\di\Proxiable;
use vivace\di\Resolver;
use vivace\di\Scope;

abstract class Package implements Scope, Proxiable
{


    /** @var  Branch */
    private $branch;
    /** @var Proxy */
    private $proxy;
    /** @var Node */
    private $node;

    public function __construct()
    {
        $this->export('vivace\di\Resolver', function (Scope $scope) {
            return new Resolver($scope);
        });
    }

    /**
     * @return Branch
     */
    private function getBranch()
    {
        return $this->branch ?? $this->branch = new Branch();
    }

    private function getProxy()
    {
        return $this->proxy ?? $this->proxy = new Proxy($this->getBranch());
    }

    private function getNode()
    {
        return $this->node ?? $this->node = new Node($this->getProxy(), new Autowire());
    }

    final protected function export(string $id, callable $factory)
    {
        if ($this->getBranch()->has($id)) {
            throw new BadDefinitionError("Factory $id has been defined");
        }
        $this->getBranch()->set($id, $factory);
    }

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
        return $this->getNode()->import($id);
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

    public function get($id)
    {
        return $this->getNode()->get($id);
    }

    public function has($id)
    {
        return $this->getNode()->has($id);
    }
}
