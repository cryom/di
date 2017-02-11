<?php
namespace vivace\di;

use vivace\di\error\IdentifierConflict;
use vivace\di\error\RecursiveDependency;
use vivace\di\error\Undefined;

/**
 * Class Scope
 * @package vivace\di
 */
abstract class Bundle implements type\Scope
{
    /** @var callable[] */
    private $producers = [];

    /** @var Composite */
    private $inherited;

    /** @var array string[] */
    private $stack = [];

    /** @inheritdoc */
    public function import(string $id)
    {
        if (in_array($id, $this->stack)) {
            throw new RecursiveDependency("Recursive dependency $id");
        }
        $this->stack[] = $id;
        $producer = $this->getProducer($id);
        try {
            $result = $producer($this);
            array_pop($this->stack);
            return $result;
        } catch (\Exception $e) {
            $this->stack = [];
            throw $e;
        }
    }

    /** @inheritdoc */
    public function getProducer(string $id): \Closure
    {

        if (isset($this->producers[$id])) {
            $producer = $this->producers[$id];
            if (!$producer instanceof \Closure) {
                return function (type\Scope $scope) use ($producer) {
                    return call_user_func($producer, $scope);
                };
            }
            return $producer;
        }

        if (!$this->inherited) {
            throw new Undefined("Undefined index $id");
        }

        return $this->getInherited()->getProducer($id);
    }

    /**
     * @return type\Composite
     */
    private function getInherited(): type\Composite
    {
        return $this->inherited ?? $this->inherited = new Composite();
    }

    /**
     * @param type\Scope $scope
     * @return type\Proxy
     */
    public function inherit(type\Scope $scope): type\Proxy
    {
        $scope = new Proxy($scope);
        $this->getInherited()->append($scope);
        return $scope;
    }

    /**
     * @param callable $producer
     * @return callable
     */
    public function bindTo(callable $producer): callable
    {
        return function (type\Scope $scope) use ($producer) {
            return call_user_func($producer, (new Composite($scope, $this)));
        };
    }

    /**
     * @param string $id
     * @param callable $producer
     * @return $this|type\Scope
     * @throws IdentifierConflict
     */
    final protected function export(string $id, callable $producer): type\Scope
    {
        if (isset($this->producers[$id])) {
            throw new IdentifierConflict("Name conflict. Identifier $id already declared");
        }
        $this->producers[$id] = $producer;
        return $this;
    }

    /**
     * @return array
     */
    protected function getProducers(): array
    {
        return $this->producers;
    }
}
