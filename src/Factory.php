<?php
/**
 * Created by PhpStorm.
 * User: albertsultanov
 * Date: 03.03.17
 * Time: 2:31
 */

namespace vivace\di;


interface Factory
{
    public function asService($value = true): Factory;

    /**
     * @param array $arguments
     * @return Factory
     */
    public function setArguments(array $arguments): Factory;

    /**
     * @param callable $function
     * @return Factory
     */
    public function setUp(callable $function): Factory;

    /**
     * @param Scope $scope
     * @return mixed
     * @throws ImportFailureError
     */
    public function __invoke(Scope $scope);

}