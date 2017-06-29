<?php
namespace vivace\di\Factory;

use vivace\di\Scope;

/**
 * Once created, the instance always returns the same instance of the object
 * @package vivace\di\Factory
 */
class Persistent extends Instance
{
    /** @var object */
    protected $instance;

    /** @inheritdoc */
    protected function produce(Scope $scope)
    {
        return $this->instance ?? $this->instance = parent::produce($scope);
    }
}
