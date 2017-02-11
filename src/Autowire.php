<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 11.02.17
 * Time: 5:05
 */

namespace vivace\di;


use vivace\di\error\Undefined;
use vivace\di\type\Injector;
use vivace\di\type\Meta;
use vivace\di\type\Scope;

/**
 * Class AutoWire
 * @package vivace\di
 */
class AutoWire extends \vivace\di\Bundle
{

    /**
     * AutoWire constructor.
     */
    public function __construct()
    {
        $this->export(Injector::class, function (Scope $scope) {
            return new \vivace\di\Injector($scope, $this->import(Meta::class));
        });

        $this->export(Meta::class, function () {
            return new \vivace\di\Meta();
        });
    }

    /**
     * @param string $id
     * @return \Closure
     * @throws Undefined
     */
    public function getProducer(string $id): \Closure
    {
        try {
            return parent::getProducer($id);
        } catch (Undefined $e) {
            if (!class_exists($id)) {
                throw $e;
            }
            return function (Scope $scope) use ($id) {
                try {
                    return $scope->import($id);
                } catch (Undefined $e) {
                    /** @var Injector $injector */
                    $injector = $scope->import(Injector::class);
                    return $injector->new($id);
                }
            };
        }
    }
}
