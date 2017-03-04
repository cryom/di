<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 27.02.17
 * Time: 1:53
 */

namespace vivace\di\Factory;

use vivace\di\BadDefinitionError;
use vivace\di\Factory;
use vivace\di\ImportFailureError;
use vivace\di\NotResolvedError;
use vivace\di\Resolver;
use vivace\di\Scope;


/**
 * Class Factory
 * @package vivace\di
 */
class Instance implements Factory
{
    /** @var bool */
    private $service = false;
    /** @var */
    private $instance;
    /** @var array */
    private $arguments = [];
    /** @var callable */
    private $setUp;
    /** @var string */
    private $className;

    /**
     * Factory constructor.
     * @param string $className
     * @param array $parameters
     * @param bool $asService
     * @throws BadDefinitionError
     */
    public function __construct(string $className, array $parameters = [], $asService = false)
    {
        if (!class_exists($className)) {
            throw new BadDefinitionError("Class $className not found");
        }
        $this->className = $className;
        $this->setParameters($parameters);
        $this->asService($asService);
    }

    /**
     * @param bool $value
     * @return Factory
     */
    public function asService($value = true): Factory
    {
        $this->service = $value;
        return $this;
    }

    /**
     * @param array $arguments
     * @return Factory
     */
    public function setParameters(array $arguments): Factory
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @param callable $function
     * @return Factory
     */
    public function setUp(callable $function): Factory
    {
        $this->setUp = $function;
        return $this;
    }

    public function produce(Scope $scope)
    {
        if (isset($this->instance)) {
            return $this->instance;
        }
        /** @var Resolver $resolver */
        $resolver = $scope->import(Resolver::class);
        try {
            $arguments = $resolver->resolve($this->className, $this->arguments);
        } catch (NotResolvedError $e) {
            throw new ImportFailureError("Import failure: " . $e->getMessage(), 0, $e);
        }
        $object = new $this->className(...$arguments);
        if (!empty($this->setUp)) {
            call_user_func($this->setUp, $object, $scope);
        }
        if ($this->service) {
            $this->instance = $object;
        }
        return $object;
    }

    /**
     * @param Scope $scope
     * @return mixed
     * @throws ImportFailureError
     */
    public function __invoke(Scope $scope)
    {
        return $this->produce($scope);
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }
}