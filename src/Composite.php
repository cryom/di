<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 24.02.17
 * Time: 0:13
 */

namespace vivace\di;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use vivace\di\exception\ImportFailure;
use vivace\di\exception\NotFound;

class Composite implements ContainerInterface, Scope
{

    /** @var Scope[] */
    private $containers;

    public function __construct(ContainerInterface ...$containers)
    {
        $this->containers = $containers;
    }

    /** @inheritdoc */
    public function import(string $id)
    {
        foreach ($this->containers as $scope) {
            try {
                $factory = $scope->get($id);
                return call_user_func($factory, $this);
            } catch (NotFound $e) {
                continue;
            }
        }
        throw new ImportFailure($e->getMessage(), 0, $e);
    }

    /** @inheritdoc */
    public function get($id)
    {
        foreach ($this->containers as $scope) {
            try {
                return $scope->get($id);
            } catch (NotFoundExceptionInterface $e) {
                continue;
            }
        }
        throw $e;
    }

    /** @inheritdoc */
    public function has($id)
    {
        foreach ($this->containers as $scope) {
            if ($scope->has($id)) {
                return true;
            }
        }
        return false;
    }
}