<?php
namespace vivace\di;

use vivace\di\error\Undefined;
use vivace\di\type;

/**
 * Class Proxy
 * @package vivace\di
 */
class Proxy extends Scope implements type\Proxy
{
    /** @var Scope */
    private $scope;

    /**
     * Proxy constructor.
     * @param type\Scope $scope
     */
    public function __construct(type\Scope $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param $source
     * @param $name
     * @return type\Proxy
     */
    public function as(string $source, string $name): type\Proxy
    {
        $this->export($name, function (type\Scope $scope) use ($source) {
            return (new Composite($this, $scope))->import($source);
        });
        return $this;
    }

    /**
     * @param $source
     * @param $name
     * @return type\Proxy
     */
    public function insteadOf(string $source, string $name): type\Proxy
    {
        $this->export($source, function (type\Scope $scope) use ($name) {
            return $scope->import($name);
        });
        return $this;
    }


    /** @inheritdoc */
    public function getProducer(string $id): \Closure
    {
        try {
            $producer = parent::getProducer($id);
        } catch (Undefined $e) {
            $producer = $this->scope->getProducer($id);
        }
        return function (type\Scope $scope) use ($producer) {
            $scope = $this->delegateScope($scope);
            return call_user_func($producer, $scope);
        };
    }

    /**
     * @param type\Scope $scope
     * @return Composite
     */
    private function delegateScope(type\Scope $scope)
    {
        return new Composite(new Container($this->getProducers()), $scope, $this->scope);
    }

    /** @inheritdoc */
    public function bind(string $id, type\Scope $scope): type\Proxy
    {
        $this->export($id, function (type\Scope $main) use ($id, $scope) {
            $scope = $this->delegateScope($main)->prepend($scope);
            return call_user_func($this->scope->getProducer($id), $scope);
        });
        return $this;
    }
}
