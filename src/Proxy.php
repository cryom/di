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
     * @param $id
     * @param $name
     * @return type\Proxy
     */
    public function as(string $id, string $name): type\Proxy
    {
        $this->export($name, function (type\Scope $scope) use ($id) {
            return (new Composite($this, $scope))->import($id);
        });
        return $this;
    }

    /**
     * @param $id
     * @param $name
     * @return type\Proxy
     */
    public function insteadOf(string $id, string $name): type\Proxy
    {
        $this->export($id, function (type\Scope $scope) use ($name) {
            return (new Composite($scope, $this))->import($name);
        });
        return $this;
    }


    /** @inheritdoc */
    public function fetch(string $id): \Closure
    {
        try {
            return parent::fetch($id);
        } catch (Undefined $e) {
        }
        return $this->scope->fetch($id);
    }

    /** @inheritdoc */
    public function bind(string $id, type\Scope $scope): type\Proxy
    {
        $this->export($id, function (type\Scope $main) use ($scope) {
            return new Composite($scope, $main, $this);
        });
    }
}
