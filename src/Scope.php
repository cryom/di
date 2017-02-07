<?php
namespace vivace\di;

use vivace\di\error\IdentifierConflict;
use vivace\di\error\RecursiveDependency;

/**
 * Class Scope
 * @package vivace\di
 */
abstract class Scope implements type\Scope
{
    /** @var callable[] */
    private $items = [];

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
        $factory = $this->fetch($id);
        try {
            return $factory($this);
        } catch (\Exception $e) {
            $this->stack = [];
            throw $e;
        }
    }

    /** @inheritdoc */
    public function fetch(string $id): \Closure
    {

        if (isset($this->items[$id])) {
            $factory = $this->items[$id];
            return function (type\Scope $scope) use ($factory) {
                return call_user_func($factory, $scope);
            };
        }
        return $this->getInherited()->fetch($id);
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
     * @param callable $factory
     * @return callable
     */
    public function bindTo(callable $factory): callable
    {
        return function (type\Scope $scope) use ($factory) {
            return call_user_func($factory, (new Composite($scope, $this)));
        };
    }

    /**
     * @param string $id
     * @param callable $factory
     * @return $this|type\Scope
     * @throws IdentifierConflict
     */
    final protected function export(string $id, callable $factory): type\Scope
    {
        if (isset($this->items[$id])) {
            throw new IdentifierConflict("Name conflict. Identifier $id already declared");
        }
        $this->items[$id] = $factory;
        return $this;
    }
}
