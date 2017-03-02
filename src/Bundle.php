<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 28.02.17
 * Time: 1:47
 */

namespace vivace\di;


use Psr\Container\ContainerInterface;
use vivace\di\Container\Proxy;
use vivace\di\Scope\Branch;
use vivace\di\Scope\Node;

abstract class Bundle
{
    /** @var callable[] */
    private $factories = [];
    /** @var ContainerInterface */
    private $use = [];

    private $entry = 'main';

    final protected function export(string $id, callable $factory)
    {
        $this->factories[$id] = $factory;
    }

    final protected function use(ContainerInterface $container): Proxiable
    {
        if (!$container instanceof Proxiable) {
            $container = new Proxy($container);
        }
        return $this->use[] = $container;
    }

    private function getScope(): Scope
    {
        $self = new Branch($this->factories);
        if (empty($this->use)) {
            return $self;
        }
        return new Node($self, ...$this->use);
    }
}
