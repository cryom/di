<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 04.02.17
 * Time: 15:50
 */

namespace vivace\di\type;


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