<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 05.02.17
 * Time: 15:32
 */

namespace vivace\di;


use vivace\di\type;

/**
 * Class Container
 * @package vivace\di
 */
class Container extends Scope
{

    /**
     * Container constructor.
     * @param array $factories Map of factories. <br/>
     *      Example: <br/>
     *      [YouClass::class => 'YouFactoryFunction', ...]
     *
     * @param type\Scope[]|array[] ...$parents <br/>
     *          Example: <br/>
     *          $container = new Container(
     *              [...],
     *              new ParentScope(),
     *              [new OtherScope(), 'as' => ['source' => 'alias'], 'insteadOf' => ['source' => 'alias']]
     *          )
     */
    public function __construct(array $factories, ...$parents)
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
        }
    }
}