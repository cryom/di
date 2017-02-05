<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 04.02.17
 * Time: 19:18
 */

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
    public function as ($source, $name): type\Proxy
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
    public function insteadOf($source, $name): type\Proxy
    {
        $this->export($source, function (type\Scope $scope) use ($name) {
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
}