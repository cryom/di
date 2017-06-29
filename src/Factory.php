<?php
namespace vivace\di;

interface Factory
{
    /**
     * The name of the class whose instances the factory is dealing with
     * @return string
     */
    public function getClassName(): string;
    /**
     * Set arguments to pass to the constructor when creating an object instance.
     * @param array $arguments The key can be a position, a name, or an argument type.
     * @return Factory Instance of the current object
     */
    public function setArguments(array $arguments): Factory;

    /**
     * Function for calling after instantiating an object and applying to it.
     * @param callable $function The function takes the first argument as an instance of the created object.
     *                           Apply to call some methods or set parameters through the methods of the setter.
     * @return Factory Instance of the current object
     */
    public function setUp(callable $function): Factory;

    /**
     * Initializing an object
     * @param Scope $scope The scope that the factory can use to resolve dependencies
     * @return object The instance of the created object
     * @throws ImportFailureError Throw away if no candidate is found to resolve the dependency
     */
    public function __invoke(Scope $scope);
}
