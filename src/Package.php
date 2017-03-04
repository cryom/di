<?php
namespace vivace\di;

use Psr\Container\ContainerInterface;
use vivace\di\Container\Proxy;
use vivace\di\Scope\Branch;
use vivace\di\Scope\Node;

trait Package
{
    /** @var callable[] */
    private $factories = [];
    /** @var ContainerInterface */
    private $use = [];

    final protected function export(string $id, callable $factory)
    {
        if (isset($this->factories[$id])) {
            throw new BadDefinitionError("Factory $id has been defined");
        }
        $this->factories[$id] = $factory;
    }

    final protected function use(ContainerInterface $container): Proxiable
    {
        if (!$container instanceof Proxiable) {
            $container = new Proxy($container);
        }
        return $this->use[] = $container;
    }

    public function getScope(): Scope
    {
        $self = new Branch($this->factories);
        if (empty($this->use)) {
            return $self;
        }
        return new Node($self, ...$this->use);
    }
}
