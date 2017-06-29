<?php
namespace vivace\di\Scope;

use Psr\Container\ContainerInterface;
use vivace\di\BadDefinitionError;
use vivace\di\Container\Autowire;
use vivace\di\Container\Proxy;
use vivace\di\Factory;
use vivace\di\Factory\Instance;
use vivace\di\ImportFailureError;
use vivace\di\Proxiable;
use vivace\di\Resolver;
use vivace\di\Scope;

/**
 * Class Package
 * @package vivace\di\Scope
 */
abstract class Package extends Proxy implements Scope, Proxiable
{
    /**
     * @var Node for support 'use' method
     * @see Package::use()
     */
    private $node;
    /**
     * @var  Autowire For support auto-wiring
     */
    private $autowire;

    /** @var  Node Node with Autowire container */
    private $autowiredNode;

    /**
     * Package constructor.
     */
    public function __construct()
    {
        $container = new Branch([
            'vivace\di\Resolver' => function (Scope $scope) {
                return new Resolver($scope);
            },
        ]);
        $this->autowire = new Autowire();
        $this->node = new Node($container);
        parent::__construct($this->node);
    }

    /**
     * @return \Psr\Container\ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->autowiredNode ?? $this->autowiredNode = new Node(parent::getContainer(), $this->autowire);
    }

    /**
     * @param string $id
     * @param callable $factory
     * @throws BadDefinitionError
     */
    final protected function export(string $id, callable $factory)
    {
        if (isset($this->items[$id])) {
            throw new BadDefinitionError("Factory $id has been defined");
        }
        parent::set($id, $factory);
    }

    /**
     * @param ContainerInterface $container
     * @return Proxiable
     */
    final protected function use(ContainerInterface $container): Proxiable
    {
        if (!$container instanceof Proxiable) {
            $container = new Proxy($container);
        }
        $this->node->append($container);
        return $container;
    }


    /** @inheritdoc */
    public function import(string $id)
    {
        if (null !== ($factory = $this->get($id))) {
            return call_user_func($factory, $this);
        }
        throw new ImportFailureError("Undefined $id");
    }

    /**
     * Settings for auto-wiring factories
     * @param string $className Name of the class you want to configure
     * @return \vivace\di\Factory
     *
     * $this->auto('PDO')->setArguments(['dsn' => 'mysql://...'])
     */
    protected function auto(string $className): Factory
    {
        return $this->autowire->get($className);
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
