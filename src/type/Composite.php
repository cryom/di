<?php
namespace vivace\di\type;

/**
 * Interface Composite
 * @package vivace\di\type
 */
interface Composite extends Scope
{
    /**
     * @param Scope $scope
     * @return Composite
     */
    public function append(Scope $scope): Composite;

    /**
     * @param Scope $scope
     * @return Composite
     */
    public function prepend(Scope $scope): Composite;
}
