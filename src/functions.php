<?php
namespace vivace\di {

    const VERSION = '0.0.1';

    /**
     * wrap value to callable
     * @param $value
     * @return callable
     */
    function wrap($value): callable
    {
        if (is_callable($value)) {
            return $value;
        }

        return function () use ($value) {
            return $value;
        };
    }
}

namespace vivace\di\resolve {

    use vivace\di\ImportFailureError;
    use vivace\di\NotResolvedError;
    use vivace\di\Scope;

    function byPosition(array $meta, array $parameters)
    {
        $pos = $meta[0];
        if (isset($parameters[$pos])) {
            return $parameters[$pos];
        }
        throw new NotResolvedError('Not resolved by pos');
    }

    function byName(array $meta, array $parameters)
    {
        $name = $meta[1];
        if (isset($parameters[$name])) {
            return $parameters[$name];
        }
        throw new NotResolvedError('Not resolved by name');
    }

    function byTypeClass(array $meta, array $parameters, Scope $scope)
    {
        if (!empty($meta[2])) {
            $className = $meta[2];
            if (isset($parameters[$className])) {
                return $parameters[$className];
            }
            try {
                return $scope->import($className);
            } catch (ImportFailureError $e) {
                //pass
            }
        }

        throw new NotResolvedError('Not resolved by class type hinting');
    }

    function byDefaultValue(array $meta)
    {
        if (array_key_exists(3, $meta)) {
            return $meta[3];
        }
        throw new NotResolvedError("Not resolved by default value");
    }
}
