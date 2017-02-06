<?php
namespace vivace\di;

use vivace\di\error\RecursiveDependency;
use vivace\di\error\Undefined;
use vivace\di\type\Scope;

/**
 * Class Composite
 * @package vivace\di
 */
class Composite implements type\Composite
{

    /** @var Scope[] */
    private $scopes;

    /**
     * Composite constructor.
     * @param Scope[] ...$scopes
     */
    public function __construct(Scope ...$scopes)
    {
        $this->scopes = $scopes;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws RecursiveDependency
     * @throws Undefined
     */
    public function import(string $id)
    {
        $factory = $this->fetch($id);
        return $factory($this);
    }

    /** @inheritdoc */
    public function fetch(string $id): \Closure
    {
        foreach ($this->scopes as $scope) {
            try {
                return $scope->fetch($id);
            } catch (Undefined $e) {
            }
        }
        throw new Undefined("Undefined index $id");
    }

    /**
     * @param callable $factory
     * @return callable
     */
    public function bind(callable $factory): callable
    {
        return function (Scope $scope) use ($factory) {
            return call_user_func($factory, new self($scope, $this));
        };
    }

    /** @inheritdoc */
    public function append(Scope $scope): type\Composite
    {
        $this->scopes[] = $scope;
        return $this;
    }

    /** @inheritdoc */
    public function prepend(Scope $scope): type\Composite
    {
        array_unshift($this->scopes, $scope);
        return $this;
    }
}
