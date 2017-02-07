<?php
namespace Helper;

use vivace\di;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

/**
 * Class Unit
 * @package Helper
 */
class Unit extends \Codeception\Module
{
    /**
     * @param array $factories
     * @param array ...$parents
     * @return di\Scope
     */
    public function newScope(array $factories, ...$parents): di\Scope
    {
        return new class($factories, $parents) extends di\Scope
        {
            /**
             *  constructor.
             * @param array $factories
             * @param array $parents
             */
            public function __construct(array $factories, array $parents)
            {
                foreach ($factories as $id => $factory) {
                    if (!is_callable($factory)) {
                        $value = $factory;
                        $factory = function () use ($value) {
                            return $value;
                        };
                    }
                    $this->export($id, $factory);
                }
                foreach ($parents as $parent) {
                    $options = [];
                    if (is_array($parent)) {
                        list($parent, $options) = [array_shift($parent), $parent];
                    }
                    $proxy = $this->inherit($parent);

                    if (isset($options['as'])) {
                        foreach ($options['as'] as $source => $alias) {
                            $proxy->as($source, $alias);
                        }
                    }

                    if (isset($options['insteadOf'])) {
                        foreach ($options['insteadOf'] as $source => $alias) {
                            $proxy->insteadOf($source, $alias);
                        }
                    }

                    if (isset($options['bind'])) {
                        foreach ($options['bind'] as $id => $scope) {
                            $proxy->bind($id, $scope);
                        }
                    }
                }
            }
        };
    }
}
