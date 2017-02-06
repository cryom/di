<?php
namespace vivace\di\type;

/**
 * Interface Meta
 * @package vivace\di\type
 */
interface Meta
{
    /**
     * Create Reflection of function or method
     * @param string|callable $target
     * Examples of input values and the result
     *      [YouClass::class, 'methodName'] - return ReflectionMethod
     *      [$object, 'methodName'] - return ReflectionMethod
     *      'YouClass::methodName' - return ReflectionMethod
     *      function(){} - return ReflectionFunction
     *      YouClass - return ReflectionMethod for __constructor, if constructor defined
     *      'youGlobalFunction' - return ReflectionFunction
     * @return null|\ReflectionFunctionAbstract null if target Reflection not supported
     */
    public function reflect($target): ?\ReflectionFunctionAbstract;

    /**
     * Get metadata of dependencies
     * @param string|callable $target
     *      It is the same as in the method Meta::reflect()
     * @return array In result you see array, contains items with the following features. <br/>
     *      (string) name - Name of property <br/>
     *      (string) type - Name of class or builtin types. Not available if type not declared. <br/>
     *      (string) default - Default value, not available if default value not set. <br/>
     * @see Meta::reflect()
     */
    public function dependencies($target): array;
}
