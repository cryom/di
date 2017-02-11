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

    /** @var Bundle[] */
    private $scopes;
    /** @var Bundle[][] */
    private static $stack = [];
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
        $factory = $this->getProducer($id);
        return $factory($this);
    }

    /** @inheritdoc */
    public function getProducer(string $id): \Closure
    {
        foreach ($this->scopes as $key => $scope) {
            if (isset(self::$stack[$id]) && in_array($scope, self::$stack[$id])) {
                continue;
            }
            try {
                $producer = $scope->getProducer($id);
            } catch (Undefined $e) {
                continue;
            }
            if (!$scope instanceof self) {
                $producer = function (Scope $main) use ($scope, $producer, $id) {
                    self::$stack[$id][] = $scope;
                    $count = count(self::$stack[$id]);
                    $offset = $count - 1;
                    $result = call_user_func($producer, $main);
                    unset(self::$stack[$id][$offset]);
                    if ($count == 1) {
                        unset(self::$stack[$id]);
                    }
                    return $result;
                };
            }
            return $producer;
        }
        throw new Undefined("Undefined index $id");
    }

    /**
     * @param callable $producer
     * @return callable
     */
    public function bindTo(callable $producer): callable
    {
        return function (Scope $scope) use ($producer) {
            return call_user_func($producer, new self($scope, $this));
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
