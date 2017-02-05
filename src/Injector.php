<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 05.02.17
 * Time: 15:18
 */

namespace vivace\di;


use vivace\di\error\NotResolved;
use vivace\di\error\Undefined;
use vivace\di\type\Meta;
use vivace\di\type\Scope;

/**
 * Class Injector
 * @package vivace\di
 */
class Injector
{
    /** @var Scope */
    private $scope;
    /** @var Meta */
    private $meta;

    /**
     * Injector constructor.
     * @param Scope $scope
     * @param Meta $meta
     */
    public function __construct(Scope $scope, Meta $meta)
    {
        $this->scope = $scope;
        $this->meta = $meta;
    }

    /**
     * @param $target
     * @return array
     * @throws NotResolved
     */
    public function resolve($target):array
    {
        $dependencies = $this->meta->dependencies($target);
        $parameters = [];
        foreach ($dependencies as $pos => $dependency) {

            if (isset($dependency['type']) && class_exists($dependency['type'])) {
                try {
                    $parameters[$pos] = $this->scope->import($dependency['type']);
                    continue;
                } catch (Undefined $e) {
                }
            }
            try {
                $parameters[$pos] = $this->scope->import($dependency['name']);
                continue;
            } catch (Undefined $e) {
            }

            if (array_key_exists('default', $dependency)) {
                $parameters[$pos] = $dependency['default'];
                continue;
            }
            $name = $dependency['name'] . '%' . $pos;
            if (isset($dependency['type'])) {
                $name = '(' . $dependency['type'] . ') ' . $name;
            }
            throw new NotResolved("Dependency $name not resolver");
        }

        return $parameters;
    }
}