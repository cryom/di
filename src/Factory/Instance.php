<?php
namespace vivace\di\Factory;

use vivace\di\BadDefinitionError;
use vivace\di\Factory;
use vivace\di\Resolver;
use vivace\di\Scope;

/**
 * Factory for instantiate object with automated resolving of dependencies
 * For resolving used instance of vivace\di\Resolver, which should be exported in your main scope
 * @package vivace\di
 */
class Instance implements Factory
{
    /** @var array */
    protected $arguments = [];
    /** @var callable */
    protected $setUp;
    /** @var string */
    protected $className;

    /**
     * Factory constructor.
     * @param string $className Target class name
     * @param array $arguments Parameters for resolver
     * @throws BadDefinitionError
     * @see Instance::setArguments
     */
    public function __construct(string $className, array $arguments = [])
    {
        $this->className = $className;
        $this->setArguments($arguments);
    }

    /** @inheritdoc */
    public function setArguments(array $arguments): Factory
    {
        $this->arguments = $arguments;
        return $this;
    }

    /** @inheritdoc */
    public function setUp(callable $function): Factory
    {
        $this->setUp = $function;
        return $this;
    }

    /**
     * Instance object of target class
     * @param Scope $scope Uses scope for resolving dependencies
     * @return object Instance object of target class
     * @throws BadDefinitionError
     */
    protected function produce(Scope $scope)
    {
        if (!class_exists($this->getClassName())) {
            throw new BadDefinitionError("Class {$this->getClassName()} not found");
        }
        /** @var Resolver $resolver */
        $resolver = $this->importResolver($scope);
        $arguments = $resolver->resolve($this->className, $this->arguments);
        $object = new $this->className(...$arguments);
        if (!empty($this->setUp)) {
            call_user_func($this->setUp, $object, $scope);
        }
        return $object;
    }

    protected function importResolver(Scope $scope): Resolver
    {
        return $scope->import(Resolver::class);
    }

    /** @inheritdoc */
    final public function __invoke(Scope $scope)
    {
        return $this->produce($scope);
    }

    /**
     * @return string return target class name
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}
