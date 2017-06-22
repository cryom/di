<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 04.03.17
 * Time: 14:04
 */

namespace vivace\di\Scope;

use vivace\di\Container\Autowire;
use vivace\di\Factory;
use vivace\di\Resolver;
use vivace\di\Scope;

abstract class Package implements Scope
{
    use \vivace\di\Package{
        getScope as private;
    }

    private $autowire;

    public function __construct()
    {
        $this->autowire = new Autowire();
        $this->use($this->autowire);

        $this->export('vivace\di\Resolver', function (Scope $scope) {
            return new Resolver($scope);
        });
    }

    /** @inheritdoc */
    public function get($id)
    {
        return $this->getScope()->get($id);
    }

    /** @inheritdoc */
    public function has($id)
    {
        return $this->getScope()->has($id);
    }

    /** @inheritdoc */
    public function import(string $id)
    {
        return $this->getScope()->import($id);
    }

    protected function define(string $className, array $arguments = []): Factory
    {
        $factory = $this->autowire->get($className);
        if ($arguments) {
            $factory->setArguments($arguments);
        }
        return $factory;
    }
}
