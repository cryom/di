<?php
namespace vivace\di\Container;

use Psr\Container\ContainerInterface;
use vivace\di\Factory;
use vivace\di\Factory\Instance;
use vivace\di\ImportFailureError;
use vivace\di\Resolver;
use vivace\di\Scope;
use vivace\di\Scope\Node;

class Autowire implements ContainerInterface
{
    private $factories = [];

    /** @inheritdoc */
    public function get($id):?Factory
    {
        if (isset($this->factories[$id])) {
            return $this->factories[$id];
        } elseif (!$this->has($id)) {
            return null;
        }
        return new class($id, $this->factories) extends Instance
        {
            /** @var */
            private $factories;

            public function __construct($className, &$factories)
            {
                parent::__construct($className);
                $this->factories = &$factories;
            }

            private function bind()
            {
                if (isset($this->factories) && !isset($this->factories[$this->getClassName()])) {
                    $this->factories[$this->getClassName()] = $this;
                    unset($this->factories);
                }
            }

            public function setArguments(array $arguments): Factory
            {
                $this->bind();
                return parent::setArguments($arguments);
            }

            public function setUp(callable $function): Factory
            {
                $this->bind();
                return parent::setUp($function);
            }

            public function produce(Scope $scope)
            {
                try {
                    return $scope->import($this->getClassName());
                } catch (ImportFailureError $e) {
                    $scope = new Node($scope, new Base([
                        Resolver::class => function (Scope $scope) {
                            return new Resolver($scope);
                        },
                    ]));
                    return parent::produce($scope);
                }
            }
        };
    }

    /** @inheritdoc */
    public function has($id): bool
    {
        return class_exists($id);
    }
}
